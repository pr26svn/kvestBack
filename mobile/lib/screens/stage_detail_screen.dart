import 'package:flutter/material.dart';
import '../models/stage.dart';
import '../models/task.dart';
import '../services/api_service.dart';

class StageDetailScreen extends StatefulWidget {
  final StageModel stage;

  const StageDetailScreen({super.key, required this.stage});

  @override
  State<StageDetailScreen> createState() => _StageDetailScreenState();
}

class _StageDetailScreenState extends State<StageDetailScreen> {
  final ApiService _api = ApiService();
  final TextEditingController _answerController = TextEditingController();
  final List<String> _selectedOptions = [];
  String _selectedOption = '';
  late Future<List<TaskModel>> _tasksFuture;
  bool _isSubmitting = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _tasksFuture = _loadTasks();
  }

  Future<List<TaskModel>> _loadTasks() async {
    _answerController.clear();
    _selectedOptions.clear();
    _selectedOption = '';
    return _api.fetchStageTasks(widget.stage.id);
  }

  Future<void> _submitAnswer(TaskModel task) async {
    setState(() {
      _errorMessage = null;
      _isSubmitting = true;
    });

    try {
      if (task.taskType == 'single_choice') {
        if (_selectedOption.isEmpty) {
          setState(() {
            _errorMessage = 'Выберите один вариант ответа.';
          });
          return;
        }
        await _api.submitTask(task.id, 1, selectedAnswers: [_selectedOption]);
      } else if (task.taskType == 'multiple_choice') {
        if (_selectedOptions.isEmpty) {
          setState(() {
            _errorMessage = 'Выберите хотя бы один вариант ответа.';
          });
          return;
        }
        await _api.submitTask(task.id, 1, selectedAnswers: List.from(_selectedOptions));
      } else {
        final answerText = _answerController.text.trim();
        if (answerText.isEmpty) {
          setState(() {
            _errorMessage = 'Введите ответ.';
          });
          return;
        }
        await _api.submitTask(task.id, 1, answerText: answerText);
      }

      setState(() {
        _tasksFuture = _loadTasks();
      });
    } catch (error) {
      setState(() {
        _errorMessage = 'Не удалось отправить ответ.';
      });
    } finally {
      setState(() {
        _isSubmitting = false;
      });
    }
  }

  Widget _buildChoiceField(TaskModel task) {
    final choices = task.choices;
    if (choices.isEmpty) {
      return const Text('Нет вариантов ответа.');
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: choices.map((choice) {
        if (task.taskType == 'single_choice') {
          return RadioListTile<String>(
            title: Text(choice.label),
            value: choice.value,
            groupValue: _selectedOption,
            onChanged: (value) {
              setState(() {
                _selectedOption = value ?? '';
              });
            },
          );
        }

        final isSelected = _selectedOptions.contains(choice.value);
        return CheckboxListTile(
          title: Text(choice.label),
          value: isSelected,
          onChanged: (checked) {
            setState(() {
              if (checked == true) {
                _selectedOptions.add(choice.value);
              } else {
                _selectedOptions.remove(choice.value);
              }
            });
          },
        );
      }).toList(),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.stage.title),
      ),
      body: FutureBuilder<List<TaskModel>>(
        future: _tasksFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError) {
            return Center(child: Text('Ошибка: ${snapshot.error}'));
          }

          final tasks = snapshot.data ?? [];
          final activeTask = tasks.firstWhere(
            (task) => task.active,
            orElse: () => tasks.isNotEmpty ? tasks.last : TaskModel(
              id: 0,
              questStageId: widget.stage.id,
              title: 'Нет задач',
              description: '',
              taskType: 'essay',
              required: false,
              completed: false,
              active: false,
              locked: false,
              payload: {},
            ),
          );

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(widget.stage.description, style: const TextStyle(fontSize: 16)),
                const SizedBox(height: 18),
                Text('Задачи этапа: ${tasks.length}', style: const TextStyle(fontWeight: FontWeight.bold)),
                const SizedBox(height: 12),
                ...tasks.map((task) {
                  return Card(
                    margin: const EdgeInsets.only(bottom: 12),
                    child: ListTile(
                      title: Text(task.title),
                      subtitle: Text(task.description),
                      trailing: Text(task.completed ? 'Выполнено' : task.locked ? 'Заблокировано' : task.active ? 'Активно' : 'Ожидает'),
                    ),
                  );
                }).toList(),
                const SizedBox(height: 24),
                if (activeTask.id != 0 && activeTask.active)
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Текущая задача', style: Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 12),
                      Card(
                        child: Padding(
                          padding: const EdgeInsets.all(16),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(activeTask.title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                              const SizedBox(height: 8),
                              Text(activeTask.description),
                              const SizedBox(height: 16),
                              if (activeTask.taskType == 'single_choice' || activeTask.taskType == 'multiple_choice')
                                _buildChoiceField(activeTask)
                              else
                                TextField(
                                  controller: _answerController,
                                  maxLines: 6,
                                  decoration: const InputDecoration(
                                    border: OutlineInputBorder(),
                                    hintText: 'Напишите ответ',
                                  ),
                                ),
                              const SizedBox(height: 16),
                              if (_errorMessage != null)
                                Text(
                                  _errorMessage!,
                                  style: const TextStyle(color: Colors.red),
                                ),
                              ElevatedButton(
                                onPressed: _isSubmitting
                                    ? null
                                    : () => _submitAnswer(activeTask),
                                child: _isSubmitting ? const CircularProgressIndicator() : const Text('Отправить ответ'),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  )
                else
                  const Text('Нет доступных заданий или все задания выполнены.'),
              ],
            ),
          );
        },
      ),
    );
  }
}
