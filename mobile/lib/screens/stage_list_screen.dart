import 'package:flutter/material.dart';
import '../models/stage.dart';
import '../services/api_service.dart';
import 'stage_detail_screen.dart';

class StageListScreen extends StatefulWidget {
  const StageListScreen({super.key});

  @override
  State<StageListScreen> createState() => _StageListScreenState();
}

class _StageListScreenState extends State<StageListScreen> {
  late Future<List<StageModel>> _stagesFuture;
  final ApiService _api = ApiService();

  @override
  void initState() {
    super.initState();
    _stagesFuture = _api.fetchStages();
  }

  Future<void> _refresh() async {
    setState(() {
      _stagesFuture = _api.fetchStages();
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Этапы квеста'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _refresh,
            tooltip: 'Обновить',
          ),
        ],
      ),
      body: FutureBuilder<List<StageModel>>(
        future: _stagesFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError) {
            return Center(child: Text('Ошибка: ${snapshot.error}'));
          }

          final stages = snapshot.data ?? [];
          if (stages.isEmpty) {
            return const Center(child: Text('Этапы не найдены.'));
          }

          return RefreshIndicator(
            onRefresh: _refresh,
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: stages.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final stage = stages[index];
                return Card(
                  child: ListTile(
                    title: Text(stage.title),
                    subtitle: Text(stage.description),
                    trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                    onTap: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(
                          builder: (_) => StageDetailScreen(stage: stage),
                        ),
                      );
                    },
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
