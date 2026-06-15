import 'package:flutter_test/flutter_test.dart';
import 'package:maskan_app/app.dart';

void main() {
  testWidgets('App builds successfully', (WidgetTester tester) async {
    await tester.pumpWidget(const MaskanApp());
    expect(find.text('مسكن'), findsOneWidget);
  });
}
