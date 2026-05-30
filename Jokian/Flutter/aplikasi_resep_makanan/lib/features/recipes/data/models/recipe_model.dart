// lib/features/recipes/data/models/recipe_model.dart

import 'package:equatable/equatable.dart';

class RecipeModel extends Equatable {
  final String id;
  final String title;
  final String? description;
  final List<String> ingredients;
  final List<Map<String, String>> steps; // [{name, description}]
  final String difficulty;
  final String? categoryId;
  final String? imageUrl;
  final String createdBy;
  final bool isApproved;
  final bool isFeatured;
  final DateTime createdAt;

  // Joined fields (not in DB directly)
  final String? categoryName;
  final String? creatorName;

  const RecipeModel({
    required this.id,
    required this.title,
    this.description,
    required this.ingredients,
    required this.steps,
    required this.difficulty,
    this.categoryId,
    this.imageUrl,
    required this.createdBy,
    required this.isApproved,
    required this.isFeatured,
    required this.createdAt,
    this.categoryName,
    this.creatorName,
  });

  factory RecipeModel.fromMap(Map<String, dynamic> map) {
    // Parse ingredients - stored as JSONB array of strings
    List<String> parseIngredients(dynamic raw) {
      if (raw == null) return [];
      if (raw is List) return raw.map((e) => e.toString()).toList();
      return [];
    }

    // Parse steps - stored as JSONB array of strings or objects
    List<Map<String, String>> parseSteps(dynamic raw) {
      if (raw == null) return [];
      if (raw is List) {
        return raw.map((e) {
          if (e is Map) {
            return {
              'name': e['name']?.toString() ?? '',
              'description': e['description']?.toString() ?? '',
            };
          }
          return {'name': '', 'description': e.toString()};
        }).toList();
      }
      return [];
    }

    return RecipeModel(
      id: map['id'] as String,
      title: map['title'] as String,
      description: map['description'] as String?,
      ingredients: parseIngredients(map['ingredients']),
      steps: parseSteps(map['steps']),
      difficulty: map['difficulty'] as String,
      categoryId: map['category_id'] as String?,
      imageUrl: map['image_url'] as String?,
      createdBy: map['created_by'] as String,
      isApproved: map['is_approved'] as bool? ?? false,
      isFeatured: map['is_featured'] as bool? ?? false,
      createdAt: DateTime.parse(map['created_at'] as String),
      categoryName: map['categories']?['name'] as String?,
      creatorName: map['profiles']?['full_name'] as String?,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'title': title,
      'description': description,
      'ingredients': ingredients,
      'steps': steps,
      'difficulty': difficulty,
      'category_id': categoryId,
      'image_url': imageUrl,
      'created_by': createdBy,
      'is_approved': isApproved,
      'is_featured': isFeatured,
    };
  }

  RecipeModel copyWith({
    String? id,
    String? title,
    String? description,
    List<String>? ingredients,
    List<Map<String, String>>? steps,
    String? difficulty,
    String? categoryId,
    String? imageUrl,
    String? createdBy,
    bool? isApproved,
    bool? isFeatured,
    DateTime? createdAt,
    String? categoryName,
    String? creatorName,
  }) {
    return RecipeModel(
      id: id ?? this.id,
      title: title ?? this.title,
      description: description ?? this.description,
      ingredients: ingredients ?? this.ingredients,
      steps: steps ?? this.steps,
      difficulty: difficulty ?? this.difficulty,
      categoryId: categoryId ?? this.categoryId,
      imageUrl: imageUrl ?? this.imageUrl,
      createdBy: createdBy ?? this.createdBy,
      isApproved: isApproved ?? this.isApproved,
      isFeatured: isFeatured ?? this.isFeatured,
      createdAt: createdAt ?? this.createdAt,
      categoryName: categoryName ?? this.categoryName,
      creatorName: creatorName ?? this.creatorName,
    );
  }

  String get cookingTime {
    final stepCount = steps.length;
    if (stepCount <= 3) return '15 mnt';
    if (stepCount <= 5) return '30 mnt';
    return '60+ mnt';
  }

  double get rating => 4.5 + (id.hashCode % 10) * 0.05;

  @override
  List<Object?> get props => [id, title, isApproved, isFeatured];
}
