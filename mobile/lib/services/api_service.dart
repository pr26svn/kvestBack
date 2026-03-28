import 'dart:convert';
import 'package:http/http.dart' as http;
import '../models/stage.dart';
import '../models/task.dart';

class ApiService {
  static const String baseUrl = 'http://10.0.2.2:8000';
  final http.Client client;

  ApiService([http.Client? client]) : client = client ?? http.Client();

  Future<List<StageModel>> fetchStages() async {
    final uri = Uri.parse('$baseUrl/api/stages');
    final response = await client.get(uri);

    if (response.statusCode != 200) {
      throw Exception('Failed to load stages');
    }

    final body = jsonDecode(response.body) as Map<String, dynamic>;
    final data = body['data'] as List<dynamic>;

    return data.map((item) => StageModel.fromJson(item as Map<String, dynamic>)).toList();
  }

  Future<List<TaskModel>> fetchStageTasks(int stageId, {int userId = 1}) async {
    final uri = Uri.parse('$baseUrl/api/stages/$stageId/tasks?userId=$userId');
    final response = await client.get(uri);

    if (response.statusCode != 200) {
      throw Exception('Failed to load tasks');
    }

    final body = jsonDecode(response.body) as Map<String, dynamic>;
    final data = body['data'] as List<dynamic>;

    return data.map((item) => TaskModel.fromJson(item as Map<String, dynamic>)).toList();
  }

  Future<void> submitTask(
    int taskId,
    int userId, {
    String? answerText,
    List<String>? selectedAnswers,
  }) async {
    final uri = Uri.parse('$baseUrl/api/submissions');
    final Map<String, dynamic> requestBody = {
      'quest_task_id': taskId,
      'user_id': userId,
      'status': 'submitted',
    };

    if (selectedAnswers != null) {
      requestBody['answer_text'] = selectedAnswers.join(', ');
      requestBody['answer_data'] = {'selected': selectedAnswers};
    } else {
      requestBody['answer_text'] = answerText ?? '';
      requestBody['answer_data'] = {'response': answerText ?? ''};
    }

    final response = await client.post(
      uri,
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode(requestBody),
    );

    if (response.statusCode != 201) {
      throw Exception('Failed to submit task');
    }
  }
}
