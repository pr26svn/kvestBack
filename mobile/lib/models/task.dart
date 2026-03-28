class TaskModel {
  final int id;
  final int questStageId;
  final String title;
  final String description;
  final String taskType;
  final bool required;
  final bool completed;
  final bool active;
  final bool locked;
  final Map<String, dynamic> payload;

  TaskModel({
    required this.id,
    required this.questStageId,
    required this.title,
    required this.description,
    required this.taskType,
    required this.required,
    required this.completed,
    required this.active,
    required this.locked,
    required this.payload,
  });

  factory TaskModel.fromJson(Map<String, dynamic> json) {
    return TaskModel(
      id: json['id'] as int,
      questStageId: json['quest_stage_id'] as int? ?? 0,
      title: json['title'] as String? ?? '',
      description: json['description'] as String? ?? '',
      taskType: json['task_type'] as String? ?? 'essay',
      required: json['required'] as bool? ?? false,
      completed: json['completed'] as bool? ?? false,
      active: json['active'] as bool? ?? false,
      locked: json['locked'] as bool? ?? false,
      payload: Map<String, dynamic>.from(json['payload'] as Map<String, dynamic>? ?? {}),
    );
  }

  List<TaskChoice> get choices {
    final rawChoices = payload['choices'] as List<dynamic>?;
    if (rawChoices == null) {
      return [];
    }

    return rawChoices
        .map((item) {
          final choice = item as Map<String, dynamic>;
          return TaskChoice(
            value: choice['value'] as String? ?? '',
            label: choice['label'] as String? ?? choice['value'] as String? ?? '',
          );
        })
        .where((choice) => choice.value.isNotEmpty)
        .toList();
  }
}

class TaskChoice {
  final String value;
  final String label;

  TaskChoice({required this.value, required this.label});
}
