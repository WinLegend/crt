<?php

namespace Nesneek\Controllers;

use Nesneek\Models\User;
use Nesneek\Config\Database;
use Nesneek\Utils\Auth;
use PDO;

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->username) && !empty($data->password) && !empty($data->invite_code)) {
            // Check invite (simple implementation: assuming invites table exists and we check it)
            // For brevity, skipping explicit Invite model class creation but implementing logic here
            $inviteQuery = "SELECT * FROM invites WHERE code = :code AND is_used = 0 LIMIT 0,1";
            $stmt = $this->db->prepare($inviteQuery);
            $stmt->bindParam(':code', $data->invite_code);
            $stmt->execute();
            
            if($stmt->rowCount() == 0) {
                http_response_code(400);
                echo json_encode(["message" => "Invalid or used invite code"]);
                return;
            }
            $invite = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if user exists
            $this->user->username = $data->username;
            if($this->user->usernameExists()) {
                http_response_code(400);
                echo json_encode(["message" => "Username already exists"]);
                return;
            }

            // Create user
            $this->user->password_hash = password_hash($data->password, PASSWORD_BCRYPT);
            $this->user->invite_code = $data->invite_code;

            if($this->user->create()) {
                // Mark invite used
                $updateInvite = "UPDATE invites SET is_used = 1, used_by = :user_id WHERE code = :code";
                $upStmt = $this->db->prepare($updateInvite);
                $upStmt->bindParam(':user_id', $this->user->id);
                $upStmt->bindParam(':code', $data->invite_code);
                $upStmt->execute();

                http_response_code(201);
                echo json_encode(["message" => "User registered successfully"]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to register user"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data"]);
        }
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->username) && !empty($data->password)) {
            $this->user->username = $data->username;
            
            if($this->user->usernameExists()) {
                if($this->user->is_blocked) {
                    http_response_code(403);
                    echo json_encode(["message" => "Account is blocked"]);
                    return;
                }

                if(password_verify($data->password, $this->user->password_hash)) {
                    $token = Auth::generateToken([
                        "id" => $this->user->id,
                        "username" => $this->user->username,
                        "role" => $this->user->role,
                        "iat" => time(),
                        "exp" => time() + (60*60*24) // 1 day
                    ]);

                    http_response_code(200);
                    echo json_encode([
                        "token" => $token,
                        "user" => [
                            "id" => $this->user->id,
                            "username" => $this->user->username,
                            "role" => $this->user->role
                        ]
                    ]);
                } else {
                    http_response_code(401);
                    echo json_encode(["message" => "Invalid credentials"]);
                }
            } else {
                http_response_code(401);
                echo json_encode(["message" => "Invalid credentials"]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data"]);
        }
    }
    
    public function me() {
        $userData = Auth::validateToken();
        if (!$userData) {
            http_response_code(401);
            echo json_encode(["message" => "Unauthorized"]);
            return;
        }
        
        $this->user->username = $userData['username'];
        $this->user->usernameExists(); // Fetch details
        
        echo json_encode([
            "id" => $this->user->id,
            "username" => $this->user->username,
            "role" => $this->user->role
        ]);
    }
}
