<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\BlacklistModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class AuthController extends ResourceController
{
    use ResponseTrait;

    public function login()
    {
        $json = $this->request->getJSON();
        $email = $json->email ?? '';
        $password = $json->password ?? '';

        $model = new UserModel();
        $user = $model->where('email', $email)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->failNotFound('Email or password is incorrect');
        }

        $key = env('JWT_KEY');
        $payload = [
            'iss' => "ci4Api", // Issuer
            'aud' => "ci4_api", // Audience
            'iat' => time(), // Issued at: time when the token was generated
            'exp' => time() + 3600, // Expiration time
            'sub' => $user['id'], // Subject
        ];

        $jwt = JWT::encode($payload, $key, 'HS256');

        // Set session for the user
        // session()->set('user_id', $user['id']);
        // Set 'isLoggedIn' session variable to true upon successful login
        session()->set('isLoggedIn', true);
        session()->set('token', $jwt);

        return $this->respond(['token' => $jwt, 'session' => session()->get()], 200);
    }

    public function register()
    {
        $json = $this->request->getJSON();
        $username = $json->username ?? '';
        $email = $json->email ?? '';
        $password = $json->password ?? '';

        // Basic validation (You should use CodeIgniter's validation library for more robust validation)
        if (empty($email) || empty($password)) {
            return $this->fail('Email and password are required', 400);
        }

        $model = new UserModel();
        // Check if email already exists
        if ($model->where('email', $email)->first()) {
            return $this->fail('Email already exists', 400);
        }

        $data = [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT), // Hash the password
        ];

        $model->save($data);

        return $this->respondCreated(['message' => 'User created successfully']);
    }

    public function getProfile()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = null;
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }
        // Check if the token is blacklisted
        $blacklistModel = new BlacklistModel();
        if ($blacklistModel->isTokenBlacklisted($token)) {
            return $this->failUnauthorized('Token is blacklisted');
        }

        try {
            $decoded = JWT::decode($token, new Key(env('JWT_KEY'), 'HS256'));
            $userId = $decoded->sub; // Assuming 'sub' contains the user ID
        } catch (Exception $e) {
            return $this->failUnauthorized('Invalid Token');
        }

        $model = new UserModel();
        $user = $model->find($userId);

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        unset($user['password']); // Remove the password before returning the user data

        return $this->respond($user);
    }

    public function logout()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = null;
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        // Check if the token is blacklisted
        $blacklistModel = new BlacklistModel();
        if ($blacklistModel->isTokenBlacklisted($token)) {
            return $this->failUnauthorized('Token is blacklisted');
        }

        if ($token) {
            // Assuming you have a model or service to handle blacklisted tokens
            $blacklistModel = new BlacklistModel();
            $blacklistModel->addToken($token);
        }
        session()->destroy();
        return $this->respond(['message' => 'User logged out successfully']);
    }

    public function getAllUsers()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = null;
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $token = $matches[1];
        }

        if (!$token) {
            return $this->failUnauthorized('Token not provided');
        }

        // Check if the token is blacklisted
        $blacklistModel = new BlacklistModel();
        if ($blacklistModel->isTokenBlacklisted($token)) {
            return $this->failUnauthorized('Token is blacklisted');
        }

        $model = new UserModel();
        $users = $model->findAll();

        // Optionally, you might want to remove sensitive data from the user records
        array_walk($users, function (&$user) {
            unset($user['password']); // Remove the password field
        });

        return $this->respond($users);
    }
}
