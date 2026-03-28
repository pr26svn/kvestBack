import 'task.dart';

class StageModel {
  final int id;
  final String title;
  final String description;
  final String stageType;
  final String? deadlineAt;
  final List<TaskModel> tasks;

  StageModel({
    required this.id,
    required this.title,
    required this.description,
    required this.stageType,
    this.deadlineAt,
    this.tasks = const [],
  });

  factory StageModel.fromJson(Map<String, dynamic> json) {
    return StageModel(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      description: json['description'] as String? ?? '',
      stageType: json['stage_type'] as String? ?? '',
      deadlineAt: json['deadline_at'] as String?,
      tasks: (json['tasks'] as List<dynamic>?)
              ?.map((item) => TaskModel.fromJson(item as Map<String, dynamic>))
              .toList() ??
          [],
    );
  }
}
