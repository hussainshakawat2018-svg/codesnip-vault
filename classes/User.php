<?php

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Register a new user
     * Returns true on success, or an error message string on failure
     */
    public function register($username, $email, $password, $confirm_password) {
        // Validate inputs
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            return "All fields are required.";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format.";
        }

        if (strlen($password) < 6) {
            return "Password must be at least 6 characters.";
        }

        if ($password !== $confirm_password) {
            return "Passwords do not match.";
        }

        // Check if username already exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            return "Username already taken.";
        }

        // Check if email already exists
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return "Email already registered.";
        }

        // Hash password and insert user
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashed_password]);

        return true;
    }

    /**
     * Login a user by email OR username
     * Returns user data array on success, or an error message string on failure
     */
    public function login($identifier, $password) {
        if (empty($identifier) || empty($password)) {
            return "All fields are required.";
        }

        // Find user by username OR email
        $stmt = $this->pdo->prepare("SELECT id, username, email, password, role, dark_mode FROM users WHERE email = ? OR username = ? LIMIT 1");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if (!$user) {
            return "Invalid username/email or password.";
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return "Invalid username/email or password.";
        }

        return $user;
    }
}
