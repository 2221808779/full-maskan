import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import '../../config/colors.dart';
import '../../core/api/api_client.dart';
import '../../l10n/app_localizations.dart';
import '../../core/widgets/maskan_scaffold.dart';
import '../../core/widgets/glass_card.dart';
import '../../core/widgets/primary_button.dart';

/// شاشة عرض الشروط والأحكام — تُجلب من الخادم أو تعرض نصاً محلياً احتياطياً مع خاصية التمرير للقبول
class TermsScreen extends StatefulWidget {
  const TermsScreen({super.key});

  @override
  State<TermsScreen> createState() => _TermsScreenState();
}

/// حالة [TermsScreen] — إدارة التمرير والقبول وتحميل الشروط
class _TermsScreenState extends State<TermsScreen> {
  bool _accepted = false;
  bool _hasScrolledToBottom = false;
  final _scrollController = ScrollController();
  String? _remoteTerms;
  bool _loadingTerms = true;

  /// Initializes scroll listener and starts fetching terms from the API.
  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    _fetchTerms();
  }

  /// Removes scroll listener and disposes the scroll controller.
  @override
  void dispose() {
    _scrollController.removeListener(_onScroll);
    _scrollController.dispose();
    super.dispose();
  }

  /// Fetches the terms and conditions text from the server API.
  Future<void> _fetchTerms() async {
    try {
      final res = await ApiClient().get('/settings/public');
      final data = res.data;
      if (data is Map && data['terms'] != null && (data['terms'] as String).isNotEmpty) {
        setState(() {
          _remoteTerms = data['terms'];
          _loadingTerms = false;
        });
      } else {
        setState(() => _loadingTerms = false);
      }
    } catch (e) {
      setState(() {
        _loadingTerms = false;
      });
    }
  }

  /// Listens to scroll position and marks terms as read when scrolled to bottom.
  void _onScroll() {
    if (!_scrollController.hasClients) return;
    final maxScroll = _scrollController.position.maxScrollExtent;
    final current = _scrollController.position.pixels;
    if (current >= maxScroll - 10 && !_hasScrolledToBottom) {
      setState(() => _hasScrolledToBottom = true);
    }
  }

  /// Pops the screen with `true` if the user has accepted the terms.
  void _agree() {
    if (!_accepted) return;
    context.pop(true);
  }

  /// Builds the terms screen with a scrollable terms text, accept checkbox,
  /// and agree button.
  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? MaskanColors.kTextPrimary : MaskanColors.lTextPrimary;
    final mutedColor = isDark ? MaskanColors.kTextSecondary : MaskanColors.lTextSecondary;
    final dividerColor = isDark ? MaskanColors.kGlassBorder : MaskanColors.lBorderSub;
    final loc = AppLocalizations.of(context);

    return MaskanScaffold(
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: Icon(Icons.close, color: textColor),
          onPressed: () => context.pop(false),
        ),
        title: Text(loc.termsAndConditions, style: TextStyle(
          color: textColor, fontFamily: 'Cairo',
        )),
        centerTitle: true,
      ),
      body: _loadingTerms
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                Expanded(
                  child: _remoteTerms != null
                      ? ListView(
                          controller: _scrollController,
                          padding: const EdgeInsets.fromLTRB(24, 8, 24, 24),
                          children: [
                            GlassCard(
                              borderRadius: 20,
                              padding: const EdgeInsets.all(24),
                              child: Text(
                                _remoteTerms!,
                                style: TextStyle(
                                  fontSize: 14,
                                  color: mutedColor,
                                  height: 1.7,
                                  fontFamily: 'Cairo',
                                ),
                              ),
                            ),
                          ],
                        )
                      : ListView(
                          controller: _scrollController,
                          padding: const EdgeInsets.fromLTRB(24, 8, 24, 24),
                          children: [
                            GlassCard(
                              borderRadius: 20,
                              padding: const EdgeInsets.all(24),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  _section(loc.introduction, [
                                    loc.termsIntroText,
                                  ]),
                                  Divider(color: dividerColor, height: 32),
                                  _section(loc.termsSection1Title, [
                                    loc.termsSection1P1,
                                    loc.termsSection1P2,
                                    loc.termsSection1P3,
                                  ]),
                                  Divider(color: dividerColor, height: 32),
                                  _section(loc.termsSection2Title, [
                                    loc.termsSection2P1,
                                    loc.termsSection2P2,
                                    loc.termsSection2P3,
                                    loc.termsSection2P4,
                                  ]),
                                  Divider(color: dividerColor, height: 32),
                                  _section(loc.termsSection3Title, [
                                    loc.termsSection3P1,
                                    loc.termsSection3P2,
                                    loc.termsSection3P3,
                                    loc.termsSection3P4,
                                  ]),
                                  Divider(color: dividerColor, height: 32),
                                  _section(loc.termsSection4Title, [
                                    loc.termsSection4P1,
                                    loc.termsSection4P2,
                                    loc.termsSection4P3,
                                  ]),
                                  Divider(color: dividerColor, height: 32),
                                  _section(loc.termsSection5Title, [
                                    loc.termsSection5P1,
                                    loc.termsSection5P2,
                                    loc.termsSection5P3,
                                    loc.termsSection5P4,
                                  ]),
                                  Divider(color: dividerColor, height: 32),
                                  _section(loc.termsSection6Title, [
                                    loc.termsSection6P1,
                                    loc.termsSection6P2,
                                    loc.termsSection6P3,
                                    loc.termsSection6P4,
                                  ]),
                                  Divider(color: dividerColor, height: 32),
                                  _section(loc.termsSection7Title, [
                                    loc.termsSection7P1,
                                    loc.termsSection7P2,
                                    loc.termsSection7P3,
                                  ]),
                                  Divider(color: dividerColor, height: 32),
                                  _section(loc.termsSection8Title, [
                                    loc.termsSection8P1,
                                    loc.termsSection8P2,
                                    loc.termsSection8P3,
                                  ]),
                                  const SizedBox(height: 16),
                                ],
                              ),
                            ),
                          ],
                        ),
                ),
                Container(
                  padding: const EdgeInsets.fromLTRB(24, 12, 24, 24),
                  decoration: BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [
                        Colors.transparent,
                        isDark ? MaskanColors.kBgDark : MaskanColors.lBg,
                      ],
                    ),
                  ),
                  child: SafeArea(
                    top: false,
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        if (!_hasScrolledToBottom)
                          Padding(
                            padding: const EdgeInsets.only(bottom: 8),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.arrow_upward, size: 14, color: MaskanColors.kBlue),
                                const SizedBox(width: 4),
                                Text(loc.pleaseReadAllTerms, style: TextStyle(
                                  fontSize: 12, color: mutedColor, fontFamily: 'Cairo',
                                )),
                              ],
                            ),
                          ),
                        Row(
                          children: [
                            SizedBox(
                              height: 24, width: 24,
                              child: Checkbox(
                                value: _accepted,
                                onChanged: _hasScrolledToBottom
                                    ? (v) => setState(() => _accepted = v ?? false)
                                    : null,
                                activeColor: MaskanColors.kBlue,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
                              ),
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                loc.agreeTerms,
                                style: TextStyle(
                                  fontSize: 14,
                                  color: _accepted ? textColor : mutedColor,
                                  fontFamily: 'Cairo',
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        PrimaryButton(
                          label: loc.agree,
                          onPressed: _accepted ? _agree : null,
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
    );
  }

  /// Builds a section of the terms with a title and bullet-pointed paragraphs.
  Widget _section(String title, List<String> paragraphs) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final textColor = isDark ? MaskanColors.kTextPrimary : MaskanColors.lTextPrimary;
    final mutedColor = isDark ? MaskanColors.kTextSecondary : MaskanColors.lTextSecondary;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: TextStyle(
          fontSize: 16,
          fontWeight: FontWeight.w700,
          color: textColor,
          fontFamily: 'Cairo',
        )),
        const SizedBox(height: 10),
        ...paragraphs.map((p) => Padding(
          padding: const EdgeInsets.only(bottom: 6),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('•  ', style: TextStyle(color: MaskanColors.kBlue)),
              Expanded(child: Text(p, style: TextStyle(
                fontSize: 13,
                color: mutedColor,
                height: 1.5,
                fontFamily: 'Cairo',
              ))),
            ],
          ),
        )),
      ],
    );
  }
}
