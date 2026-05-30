// lib/features/recipes/data/models/category_model.dart

import 'package:equatable/equatable.dart';
import 'package:flutter/material.dart';
import 'package:resepku/core/theme/app_theme.dart';

class CategoryModel extends Equatable {
  final String id;
  final String name;
  final DateTime createdAt;

  const CategoryModel({
    required this.id,
    required this.name,
    required this.createdAt,
  });

  factory CategoryModel.fromMap(Map<String, dynamic> map) {
    return CategoryModel(
      id: map['id'] as String,
      name: map['name'] as String,
      createdAt: DateTime.parse(map['created_at'] as String),
    );
  }

  String get emoji {
    switch (name.toLowerCase()) {
      case 'makanan tradisional':
        return '🏺';
      case 'dessert':
        return '🍰';
      case 'menu diet':
        return '🥗';
      case 'cepat saji':
        return '🔥';
      default:
        return '🍽️';
    }
  }

  Color get color {
    switch (name.toLowerCase()) {
      case 'makanan tradisional':
        return AppColors.catTradisional;
      case 'dessert':
        return AppColors.catDessert;
      case 'menu diet':
        return AppColors.catDiet;
      case 'cepat saji':
        return AppColors.catGrill;
      default:
        return AppColors.primary;
    }
  }

  @override
  List<Object?> get props => [id, name];
}
