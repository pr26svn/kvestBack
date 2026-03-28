import 'package:flutter/material.dart';
import 'screens/stage_list_screen.dart';

void main() {
  runApp(const MentorshipQuestApp());
}

class MentorshipQuestApp extends StatelessWidget {
  const MentorshipQuestApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Mentorship Quest',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: Colors.indigo),
        useMaterial3: true,
      ),
      home: const StageListScreen(),
    );
  }
}
