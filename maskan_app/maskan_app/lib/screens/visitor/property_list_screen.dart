import 'dart:math';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:go_router/go_router.dart';
import 'package:geolocator/geolocator.dart';
import '../../config/routes.dart';
import '../../config/colors.dart';
import '../../core/widgets/glass_card.dart';
import '../../core/widgets/property_card.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../providers/property_provider.dart';
import '../../models/property.dart';
import '../../core/utils/helpers.dart';
import '../../l10n/app_localizations.dart';

/// شاشة عرض قائمة عقارات قابلة للبحث والتصفية مع ترتيب اختياري حسب الموقع
class PropertyListScreen extends StatefulWidget {
  /// نص بحث اختياري لتصفية العقارات عند التحميل
  final String? searchQuery;
  const PropertyListScreen({super.key, this.searchQuery});

  @override
  State<PropertyListScreen> createState() => _PropertyListScreenState();
}

/// حالة [PropertyListScreen] — إدارة البحث وأزرار التصفية
/// location permission, and Haversine distance calculations.
class _PropertyListScreenState extends State<PropertyListScreen> {
  List<Property> _results = [];
  bool _isSearching = false;
  bool _hasSearchResults = false; // true بعد تنفيذ بحث محلي (مش ازاى جينا من برا)
  String _activeFilter = '';
  final _searchController = TextEditingController();

  static const _filterOptions = [
    _FilterOption('الكل', ''),
    _FilterOption('شقه', 'apartment'),
    _FilterOption('استراحة', 'rest_house'),
    _FilterOption('منتجع', 'resort'),
    _FilterOption('فيلا', 'villa'),
    _FilterOption('مبني', 'building'),
  ];


  Position? _currentPosition;
  bool _locationAllowed = false;
  bool _locationAsked = false;

  /// Initiates search or loads all properties based on [searchQuery].
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (widget.searchQuery != null) {
        _searchController.text = widget.searchQuery!;
        _performSearch();
      } else {
        _loadAll();
      }
    });
  }

  /// Disposes the search controller.
  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  /// Loads all properties from the provider.
  Future<void> _loadAll() async {
    await context.read<PropertyProvider>().loadProperties(refresh: true);
  }

  /// Searches properties by the query text and prompts for location.
  Future<void> _performSearch() async {
    final query = _searchController.text.trim();
    if (query.isEmpty) return;

    setState(() => _isSearching = true);
    _results = await context.read<PropertyProvider>().searchProperties(
      query: query,
    );
    setState(() => _hasSearchResults = true);
    if (_results.isEmpty && mounted) {
      final loc = AppLocalizations.of(context);
      Helpers.showSnackBar(context, loc.noResultsFound, isError: false);
    }
    if (!_locationAsked) {
      _locationAsked = true;
      if (mounted) await _showLocationDialog();
    }
    if (mounted) setState(() => _isSearching = false);
  }

  /// Shows a dialog asking the user to allow location access for nearby sorting.
  Future<void> _showLocationDialog() async {
    final loc = AppLocalizations.of(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final result = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: isDark ? MaskanColors.kBgCard : MaskanColors.lBgCard,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            Icon(Icons.location_on, color: MaskanColors.kBlueLight, size: 24),
            const SizedBox(width: 8),
            Expanded(child: Text(loc.locationPermissionTitle, style: TextStyle(
              color: context.textPrimary, fontFamily: 'Cairo',
            ))),
          ],
        ),
        content: Text(loc.locationPermissionDesc, style: TextStyle(
          color: context.textSecondary, fontFamily: 'Cairo', fontSize: 14,
        )),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text(loc.deny, style: TextStyle(color: context.textSecondary, fontFamily: 'Cairo')),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(loc.allow, style: const TextStyle(color: MaskanColors.kBlueLight, fontFamily: 'Cairo')),
          ),
        ],
      ),
    );
    if (result == true && mounted) {
      await _requestLocation();
    }
  }

  /// Requests location permission and retrieves the current device position.
  Future<void> _requestLocation() async {
    try {
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.whileInUse || permission == LocationPermission.always) {
        final pos = await Geolocator.getCurrentPosition(
          locationSettings: const LocationSettings(accuracy: LocationAccuracy.high),
        );
        if (mounted) {
          setState(() {
            _currentPosition = pos;
            _locationAllowed = true;
          });
        }
      }
    } catch (_) {}
  }

  /// Filters properties by the active filter chip and sorts by distance if
  /// location is available.
  List<Property> _getDisplayedProperties(List<Property> props) {
    var filtered = props;
    if (_activeFilter.isNotEmpty) {
      filtered = props.where((p) => p.propertyType == _activeFilter).toList();
    }
    if (!_locationAllowed || _currentPosition == null) return filtered;
    final sorted = List<Property>.from(filtered);
    sorted.sort((a, b) {
      final dA = _distanceToProperty(a);
      final dB = _distanceToProperty(b);
      return dA.compareTo(dB);
    });
    return sorted;
  }

  /// Calculates the Haversine distance from the current position to a property.
  double _distanceToProperty(Property p) {
    if (_currentPosition == null || p.latitude == null || p.longitude == null) return double.infinity;
    return _calculateDistance(
      _currentPosition!.latitude, _currentPosition!.longitude,
      p.latitude!, p.longitude!,
    );
  }

  /// Implements the Haversine formula to calculate distance between two coordinates.
  double _calculateDistance(double lat1, double lon1, double lat2, double lon2) {
    const R = 6371.0;
    final dLat = _toRadians(lat2 - lat1);
    final dLon = _toRadians(lon2 - lon1);
    final a = sin(dLat / 2) * sin(dLat / 2) +
        cos(_toRadians(lat1)) * cos(_toRadians(lat2)) * sin(dLon / 2) * sin(dLon / 2);
    final c = 2 * atan2(sqrt(a), sqrt(1 - a));
    return R * c;
  }

  /// Converts a degree value to radians.
  double _toRadians(double degree) => degree * pi / 180.0;

  /// Builds the property list screen with a sliver app bar, search field,
  /// filter chips, and a scrollable list of property cards.
  @override
  Widget build(BuildContext context) {
    final provider = context.watch<PropertyProvider>();
    final loc = AppLocalizations.of(context);
    final isLoading = provider.isLoading || _isSearching;
    final rawProps = widget.searchQuery != null || _hasSearchResults ? _results : provider.properties;
    final props = _getDisplayedProperties(rawProps);

    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textCol = isDark ? const Color(0xFFE8F0F8) : const Color(0xFF1B3A52);
    final mutedCol = isDark ? const Color(0xFF8FA4B8) : const Color(0xFF7B9BB5);
    final chipBg = isDark ? const Color(0x1AFFFFFF) : const Color(0x1A2D5F8A);

    return MaskanScaffold(
      body: CustomScrollView(
        slivers: [
          SliverAppBar(
            expandedHeight: 160,
            pinned: true,
            backgroundColor: Colors.transparent,
            leading: Padding(
              padding: const EdgeInsets.only(top: 4),
              child: IconButton(
                icon: Icon(Icons.arrow_back_ios, color: textCol, size: 20),
                onPressed: () => context.pop(),
              ),
            ),
            flexibleSpace: FlexibleSpaceBar(
              background: Padding(
                padding: const EdgeInsets.fromLTRB(16, kToolbarHeight + 8, 16, 0),
                child: Column(
                  children: [
                    Container(
                      decoration: BoxDecoration(
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: MaskanColors.kBlue, width: 1.5),
                        boxShadow: [
                          BoxShadow(color: MaskanColors.kBlue.withValues(alpha: 0.2), blurRadius: 10, offset: const Offset(0, 3)),
                        ],
                      ),
                      child: GlassCard(
                        blurStrength: 4,
                        borderRadius: 14,
                        height: 44,
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        child: TextField(
                          controller: _searchController,
                          style: TextStyle(
                            color: textCol, fontFamily: 'Cairo', fontSize: 14,
                          ),
                          decoration: InputDecoration(
                            hintText: AppLocalizations.of(context)!.searchHint,
                            hintStyle: TextStyle(color: mutedCol),
                            border: InputBorder.none,
                            enabledBorder: InputBorder.none,
                            focusedBorder: InputBorder.none,
                            prefixIcon: Icon(Icons.search, color: mutedCol, size: 20),
                            suffixIcon: IconButton(
                              icon: Icon(Icons.search, color: MaskanColors.kBlue, size: 22),
                              onPressed: _performSearch,
                              padding: EdgeInsets.zero,
                              splashRadius: 20,
                            ),
                            contentPadding: EdgeInsets.zero,
                          ),
                          onSubmitted: (_) => _performSearch(),
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    SizedBox(
                      height: 32,
                      child: ListView(
                        scrollDirection: Axis.horizontal,
                        children: _filterOptions.map((opt) {
                          final active = _activeFilter == opt.value;
                          return Padding(
                            padding: const EdgeInsets.only(left: 8),
                            child: ChoiceChip(
                              label: Text(opt.label, style: TextStyle(
                                fontSize: 13, fontFamily: 'Cairo',
                                color: active ? Colors.white : mutedCol,
                              )),
                              selected: active,
                              selectedColor: const Color(0xFF2D5F8A),
                              backgroundColor: chipBg,
                              onSelected: (_) => setState(() => _activeFilter = opt.value),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                              side: BorderSide(
                                color: active ? MaskanColors.kBlue : MaskanColors.kBlue.withValues(alpha: 0.4),
                                width: 1.5,
                              ),
                              shadowColor: MaskanColors.kBlue.withValues(alpha: 0.2),
                              elevation: 2,
                            ),
                          );
                        }).toList(),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 8),
              child: Row(
                children: [
                  Icon(
                    _locationAllowed ? Icons.near_me : Icons.home,
                    size: 16, color: _locationAllowed ? MaskanColors.kBlueLight : mutedCol,
                  ),
                  const SizedBox(width: 6),
                  Text(
                    _locationAllowed ? loc.nearbyProperties : loc.allPropertiesLabel,
                    style: TextStyle(fontSize: 13, color: mutedCol, fontFamily: 'Cairo'),
                  ),
                  Text(' (${props.length})', style: TextStyle(
                    fontSize: 13, color: mutedCol, fontFamily: 'Cairo',
                  )),
                  const Spacer(),
                  Icon(Icons.sort, color: MaskanColors.kBlue, size: 18),
                  const SizedBox(width: 4),
                  Text(AppLocalizations.of(context)!.sort, style: TextStyle(
                    fontSize: 13, color: MaskanColors.kBlue, fontFamily: 'Cairo',
                  )),
                ],
              ),
            ),
          ),
          if (isLoading && props.isEmpty)
            const SliverFillRemaining(child: Center(child: CircularProgressIndicator()))
          else if (props.isEmpty)
            SliverFillRemaining(
              child: Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.home_work_outlined, color: mutedCol, size: 64),
                    const SizedBox(height: 16),
                    Text(AppLocalizations.of(context)!.noPropertiesAvailable, style: TextStyle(
                      fontSize: 18, color: mutedCol, fontFamily: 'Cairo',
                    )),
                    const SizedBox(height: 4),
                    Text(AppLocalizations.of(context)!.tryChangingFilters, style: TextStyle(
                      fontSize: 14, color: mutedCol, fontFamily: 'Cairo',
                    )),
                  ],
                ),
              ),
            )
          else
            SliverList(
              delegate: SliverChildBuilderDelegate(
                (_, i) {
                  final p = props[i];
                  final dist = _locationAllowed ? _distanceToProperty(p) : null;
                  return Column(
                    children: [
                      if (dist != null && dist < 100)
                        Padding(
                          padding: const EdgeInsets.fromLTRB(24, 4, 24, 0),
                          child: Row(
                            children: [
                              Icon(Icons.near_me, size: 12, color: MaskanColors.kBlueLight),
                              const SizedBox(width: 4),
                              Text(
                                '${dist.toStringAsFixed(1)} ${loc.kilometers}',
                                style: TextStyle(fontSize: 11, color: MaskanColors.kBlueLight, fontFamily: 'Cairo'),
                              ),
                            ],
                          ),
                        ),
                      PropertyCard(
                        property: p,
                        showFavorite: false,
                        onTap: () => context.push(
                          AppRoutes.propertyDetail.replaceFirst(':id', '${p.id}'),
                        ),
                      ),
                    ],
                  );
                },
                childCount: props.length,
              ),
            ),
        ],
      ),
    );
  }
}

/// خيار تصفية — تسمية عرض وقيمة
class _FilterOption {
  final String label;
  final String value;
  const _FilterOption(this.label, this.value);
}
