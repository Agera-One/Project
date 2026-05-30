// lib/features/auth/data/models/profile_model.dart

import 'package:equatable/equatable.dart';

class ProfileModel extends Equatable {
  final String id;
  final String email;
  final String? fullName;
  final String role;
  final DateTime createdAt;

  const ProfileModel({
    required this.id,
    required this.email,
    this.fullName,
    required this.role,
    required this.createdAt,
  });

  factory ProfileModel.fromMap(Map<String, dynamic> map) {
    return ProfileModel(
      id: map['id'] as String,
      email: map['email'] as String,
      fullName: map['full_name'] as String?,
      role: map['role'] as String? ?? 'user',
      createdAt: DateTime.parse(map['created_at'] as String),
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'email': email,
      'full_name': fullName,
      'role': role,
    };
  }

  bool get isAdmin => role == 'admin';

  String get displayName => fullName ?? email.split('@').first;

  @override
  List<Object?> get props => [id, email, role];
}
