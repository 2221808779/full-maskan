import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import '../../config/colors.dart';
import '../../config/routes.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/widgets/glass_card.dart';
import '../../core/widgets/primary_button.dart';
import '../../core/utils/helpers.dart';
import '../../providers/property_provider.dart';
import '../../providers/booking_provider.dart';
import '../../models/property.dart';
import '../../l10n/app_localizations.dart';

/// شاشة نموذج الحجز — تقويم تفاعلي لاختيار التواريخ وعدد الضيوف وحساب السعر على نمط Airbnb
class BookingFormScreen extends StatefulWidget {
  /// معرّف العقار المراد حجزه
  final int propertyId;
  const BookingFormScreen({super.key, required this.propertyId});

  @override
  State<BookingFormScreen> createState() => _BookingFormScreenState();
}

/// حالة [BookingFormScreen] — إدارة اختيار التواريخ والتواريخ المحجوزة
/// guest count, and booking submission.
class _BookingFormScreenState extends State<BookingFormScreen> {
  DateTime? _startDate;
  DateTime? _endDate;
  int _guests = 1;
  bool _isSubmitting = false;
  /// The month currently displayed in the calendar.
  DateTime _viewMonth = DateTime(DateTime.now().year, DateTime.now().month);
  /// List of dates that are already booked or blocked.
  List<DateTime> _blockedDates = [];
  bool _loadingBlocked = true;

  /// Loads property details and blocked dates on initialization.
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final provider = context.read<PropertyProvider>();
      await provider.loadPropertyDetail(widget.propertyId);
      _loadBlockedDates();
    });
  }

  /// Fetches blocked/blackout dates for the property from the API.
  Future<void> _loadBlockedDates() async {
    setState(() => _loadingBlocked = true);
    try {
      final provider = context.read<BookingProvider>();
      final dates = await provider.fetchBlockedDates(widget.propertyId);
      if (mounted) setState(() => _blockedDates = dates);
    } catch (_) {
      if (mounted) setState(() => _blockedDates = []);
    } finally {
      if (mounted) setState(() => _loadingBlocked = false);
    }
  }

  /// Whether the given date is in the blocked dates list.
  bool _isDateBlocked(DateTime date) {
    return _blockedDates.any((b) =>
      b.year == date.year && b.month == date.month && b.day == date.day);
  }

  /// Whether the given date falls within the selected start-end range.
  bool _isInRange(DateTime date) {
    if (_startDate == null || _endDate == null) return false;
    return date.isAfter(_startDate!.subtract(const Duration(days: 1))) &&
           date.isBefore(_endDate!.add(const Duration(days: 1)));
  }

  /// Whether the given date is the range start or end date.
  bool _isRangeStartOrEnd(DateTime date) {
    if (_startDate != null &&
        date.year == _startDate!.year &&
        date.month == _startDate!.month &&
        date.day == _startDate!.day) {
      return true;
    }
    if (_endDate != null &&
        date.year == _endDate!.year &&
        date.month == _endDate!.month &&
        date.day == _endDate!.day) {
      return true;
    }
    return false;
  }

  /// Whether the given date is before today (in the past).
  bool _isPast(DateTime date) {
    final today = DateTime.now();
    return date.isBefore(DateTime(today.year, today.month, today.day));
  }

  /// Returns the number of nights between the selected dates.
  int _nightsCount() {
    if (_startDate == null || _endDate == null) return 0;
    return _endDate!.difference(_startDate!).inDays;
  }

  /// Handles a day tap on the calendar, setting start/end dates and
  /// checking for blocked dates in the selected range.
  void _onDayTap(DateTime date) {
    if (_isDateBlocked(date) || _isPast(date)) return;
    if (_startDate == null || (_endDate != null)) {
      setState(() {
        _startDate = date;
        _endDate = null;
      });
    } else if (date.isBefore(_startDate!)) {
      setState(() => _startDate = date);
    } else if (date.isAtSameMomentAs(_startDate!)) {
      return;
    } else {
      final range = _generateDateRange(_startDate!, date);
      final hasBlocked = range.any((d) => _isDateBlocked(d));
      if (!hasBlocked) {
        setState(() => _endDate = date);
      } else {
        Helpers.showSnackBar(context, AppLocalizations.of(context)!.blockedDatesInRange, isError: true);
      }
    }
  }

  /// Generates a list of all dates between start and end (inclusive).
  List<DateTime> _generateDateRange(DateTime start, DateTime end) {
    final days = <DateTime>[];
    var current = DateTime(start.year, start.month, start.day);
    final last = DateTime(end.year, end.month, end.day);
    while (!current.isAfter(last)) {
      days.add(current);
      current = current.add(const Duration(days: 1));
    }
    return days;
  }

  /// Returns the localized name of a month given its index (1-12).
  String _monthName(int month) {
    final loc = AppLocalizations.of(context);
    return [
      loc.month1, loc.month2, loc.month3, loc.month4, loc.month5, loc.month6,
      loc.month7, loc.month8, loc.month9, loc.month10, loc.month11, loc.month12,
    ][month - 1];
  }

  /// Submits the booking request to the server and navigates to payment.
  Future<void> _submit() async {
    if (_startDate == null || _endDate == null) {
      Helpers.showSnackBar(context, AppLocalizations.of(context)!.pleaseSelectDate, isError: true);
      return;
    }
    setState(() => _isSubmitting = true);
    final provider = context.read<BookingProvider>();
    final bookingId = await provider.createBooking(
      propertyId: widget.propertyId,
      startDate: _startDate!,
      endDate: _endDate!,
      guests: _guests,
    );
    if (!mounted) return;
    setState(() => _isSubmitting = false);
    if (bookingId != null) {
      Helpers.showSnackBar(context, AppLocalizations.of(context)!.bookingRequestSent);
      context.push(AppRoutes.payment.replaceFirst(':bookingId', '$bookingId'));
    } else {
      Helpers.showSnackBar(context, provider.error ?? AppLocalizations.of(context)!.createBookingFailed, isError: true);
    }
  }

  /// Builds the booking form screen with property info, calendar, guest count,
  /// price summary, and submit button.
  @override
  Widget build(BuildContext context) {
    final propertyProvider = context.watch<PropertyProvider>();
    final property = propertyProvider.selectedProperty;
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? MaskanColors.kTextPrimary : MaskanColors.lTextPrimary;
    final mutedColor = isDark ? MaskanColors.kTextSecondary : MaskanColors.lTextSecondary;

    return MaskanScaffold(
      appBar: AppBar(
        title: Text(AppLocalizations.of(context)!.newBooking),
        elevation: 0, scrolledUnderElevation: 0, centerTitle: true,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (property != null) ...[
              GlassCard(
                borderRadius: 16,
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    Container(
                      width: 64, height: 64,
                      decoration: BoxDecoration(
                        color: MaskanColors.kBlue.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.home, color: MaskanColors.kBlue, size: 32),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(property.title, style: TextStyle(
                            fontSize: 16, fontWeight: FontWeight.bold, color: textColor,
                            fontFamily: 'Cairo',
                          )),
                          const SizedBox(height: 4),
                          Text(property.location, style: TextStyle(
                            fontSize: 13, color: mutedColor, fontFamily: 'Cairo',
                          )),
                        ],
                      ),
                    ),
                    Text('${property.priceFormatted} ${AppLocalizations.of(context)!.currencyLyd}',
                      style: const TextStyle(
                        fontWeight: FontWeight.bold, fontSize: 18,
                        color: MaskanColors.kGold, fontFamily: 'Cairo',
                      )),
                  ],
                ),
              ),
              const SizedBox(height: 20),
            ],
            Text(AppLocalizations.of(context)!.selectBookingDates, style: TextStyle(
              fontSize: 18, fontWeight: FontWeight.bold, color: textColor, fontFamily: 'Cairo',
            )),
            const SizedBox(height: 4),
            Text(AppLocalizations.of(context)!.selectStartEndDates, style: TextStyle(
              fontSize: 13, color: mutedColor, fontFamily: 'Cairo',
            )),
            const SizedBox(height: 16),
            _buildCalendar(property, isDark, textColor, mutedColor),
            if (_loadingBlocked)
              const Padding(
                padding: EdgeInsets.all(12),
                child: Center(child: SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))),
              ),
            const SizedBox(height: 20),
            GlassCard(
              borderRadius: 14,
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(AppLocalizations.of(context)!.numberOfGuests, style: TextStyle(
                    fontSize: 15, fontWeight: FontWeight.w600, color: textColor, fontFamily: 'Cairo',
                  )),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      IconButton(
                        onPressed: _guests > 1 ? () => setState(() => _guests--) : null,
                        icon: const Icon(Icons.remove_circle_outline),
                        color: MaskanColors.kBlue,
                      ),
                      Container(
                        width: 40, alignment: Alignment.center,
                        child: Text('$_guests', style: TextStyle(
                          fontSize: 20, fontWeight: FontWeight.bold, color: textColor,
                        )),
                      ),
                      IconButton(
                        onPressed: _guests < 10 ? () => setState(() => _guests++) : null,
                        icon: const Icon(Icons.add_circle_outline),
                        color: MaskanColors.kBlue,
                      ),
                      const SizedBox(width: 8),
                      Text(AppLocalizations.of(context)!.guests, style: TextStyle(
                        color: mutedColor, fontFamily: 'Cairo',
                      )),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            if (_startDate != null && _endDate != null && property != null) ...[
              GlassCard(
                variant: GlassVariant.gold,
                borderRadius: 16,
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text('${property.price.toStringAsFixed(0)} ${AppLocalizations.of(context)!.currencyLyd} × ${_nightsCount()} ${AppLocalizations.of(context)!.nights}',
                          style: TextStyle(fontSize: 14, color: mutedColor, fontFamily: 'Cairo')),
                        Text('${(_nightsCount() * property.price).toStringAsFixed(0)} ${AppLocalizations.of(context)!.currencyLyd}',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: Colors.white, fontFamily: 'Cairo')),
                      ],
                    ),
                    const Divider(color: Colors.white24, height: 20),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(AppLocalizations.of(context)!.totalAmount, style: TextStyle(
                          fontWeight: FontWeight.bold, fontSize: 16, color: Colors.white, fontFamily: 'Cairo',
                        )),
                        Text('${(_nightsCount() * property.price).toStringAsFixed(0)} ${AppLocalizations.of(context)!.currencyLyd}',
                          style: const TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 22,
                            color: MaskanColors.kGoldLight, fontFamily: 'Cairo',
                          )),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
            ],
            PrimaryButton(
              label: _startDate == null || _endDate == null
                  ? AppLocalizations.of(context)!.chooseDates : AppLocalizations.of(context)!.confirmBooking,
              isLoading: _isSubmitting,
              onPressed: (_startDate != null && _endDate != null) ? _submit : null,
            ),
          ],
        ),
      ),
    );
  }

  /// Builds the interactive monthly calendar with blocked dates, selection
  /// highlighting, and date range labels.
  Widget _buildCalendar(Property? property, bool isDark, Color textColor, Color mutedColor) {
    final today = DateTime.now();
    final daysInMonth = DateTime(_viewMonth.year, _viewMonth.month + 1, 0).day;
    final firstWeekday = DateTime(_viewMonth.year, _viewMonth.month, 1).weekday;
    final adjustedStart = (firstWeekday + 6) % 7;

    return GlassCard(
      borderRadius: 16,
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              IconButton(
                icon: const Icon(Icons.chevron_right, color: MaskanColors.kBlue),
                onPressed: _viewMonth.month > today.month || _viewMonth.year > today.year
                    ? () => setState(() => _viewMonth = DateTime(_viewMonth.year, _viewMonth.month - 1))
                    : null,
              ),
              Text('${_monthName(_viewMonth.month)} ${_viewMonth.year}',
                style: TextStyle(
                  fontSize: 16, fontWeight: FontWeight.bold, color: textColor, fontFamily: 'Cairo',
                )),
              IconButton(
                icon: const Icon(Icons.chevron_left, color: MaskanColors.kBlue),
                onPressed: () => setState(() => _viewMonth = DateTime(_viewMonth.year, _viewMonth.month + 1)),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [AppLocalizations.of(context)!.dayAbbr1, AppLocalizations.of(context)!.dayAbbr2, AppLocalizations.of(context)!.dayAbbr3, AppLocalizations.of(context)!.dayAbbr4, AppLocalizations.of(context)!.dayAbbr5, AppLocalizations.of(context)!.dayAbbr6, AppLocalizations.of(context)!.dayAbbr7].map((d) =>
              SizedBox(
                width: 36,
                child: Text(d, textAlign: TextAlign.center, style: TextStyle(
                  fontSize: 12, fontWeight: FontWeight.bold, color: mutedColor, fontFamily: 'Cairo',
                )),
              ),
            ).toList(),
          ),
          const SizedBox(height: 8),
          ...List.generate((adjustedStart + daysInMonth + 6) ~/ 7, (weekIndex) {
            return Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: List.generate(7, (dayIndex) {
                final dayNum = weekIndex * 7 + dayIndex - adjustedStart + 1;
                if (dayNum < 1 || dayNum > daysInMonth) {
                  return const SizedBox(width: 36, height: 36);
                }
                final date = DateTime(_viewMonth.year, _viewMonth.month, dayNum);
                final blocked = _isDateBlocked(date);
                final past = _isPast(date);
                final inRange = _isInRange(date);
                final isEdge = _isRangeStartOrEnd(date);

                Color bgColor = Colors.transparent;
                Color textClr = textColor;
                if (blocked || past) {
                  textClr = mutedColor.withValues(alpha: 0.4);
                } else if (isEdge) {
                  bgColor = MaskanColors.kBlue;
                  textClr = Colors.white;
                } else if (inRange) {
                  bgColor = MaskanColors.kBlue.withValues(alpha: 0.15);
                }

                return GestureDetector(
                  onTap: () => _onDayTap(date),
                  child: Container(
                    width: 36, height: 36,
                    decoration: BoxDecoration(
                      color: bgColor,
                      borderRadius: isEdge ? BorderRadius.circular(18) : null,
                    ),
                    alignment: Alignment.center,
                    child: Text('$dayNum', style: TextStyle(
                      fontSize: 13,
                      color: textClr,
                      fontWeight: isEdge ? FontWeight.bold : FontWeight.normal,
                      fontFamily: 'Cairo',
                      decoration: blocked ? TextDecoration.lineThrough : null,
                    )),
                  ),
                );
              }),
            );
          }),
          if (_startDate != null || _endDate != null) ...[
            const SizedBox(height: 12),
            const Divider(height: 1),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                if (_startDate != null)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: MaskanColors.kBlue.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      '${AppLocalizations.of(context)!.from} ${_startDate!.day} ${_monthName(_startDate!.month)}',
                      style: TextStyle(fontSize: 13, color: MaskanColors.kBlue, fontFamily: 'Cairo'),
                    ),
                  ),
                if (_startDate != null && _endDate != null) ...[
                  const SizedBox(width: 8),
                  Icon(Icons.arrow_left, color: mutedColor, size: 20),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: MaskanColors.kBlue.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      '${AppLocalizations.of(context)!.to} ${_endDate!.day} ${_monthName(_endDate!.month)}',
                      style: TextStyle(fontSize: 13, color: MaskanColors.kBlue, fontFamily: 'Cairo'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: MaskanColors.kGold.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      '${_nightsCount()} ${AppLocalizations.of(context)!.nights}',
                      style: const TextStyle(
                        fontSize: 13, color: MaskanColors.kGold, fontWeight: FontWeight.bold, fontFamily: 'Cairo',
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ],
          const SizedBox(height: 4),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(width: 10, height: 10, decoration: BoxDecoration(
                color: MaskanColors.kBlue, shape: BoxShape.circle,
              )),
              const SizedBox(width: 4),
              Text(AppLocalizations.of(context)!.available, style: TextStyle(fontSize: 11, color: mutedColor, fontFamily: 'Cairo')),
              const SizedBox(width: 16),
              Container(width: 10, height: 10, decoration: BoxDecoration(
                color: mutedColor.withValues(alpha: 0.4), shape: BoxShape.circle,
              )),
              const SizedBox(width: 4),
              Text(AppLocalizations.of(context)!.bookedBlocked, style: TextStyle(fontSize: 11, color: mutedColor, fontFamily: 'Cairo')),
              const SizedBox(width: 16),
              Container(width: 10, height: 10, decoration: BoxDecoration(
                color: MaskanColors.kBlue.withValues(alpha: 0.15), shape: BoxShape.circle,
              )),
              const SizedBox(width: 4),
              Text(AppLocalizations.of(context)!.selected, style: TextStyle(fontSize: 11, color: mutedColor, fontFamily: 'Cairo')),
            ],
          ),
        ],
      ),
    );
  }
}