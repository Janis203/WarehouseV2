<?php

namespace app;

class Authorize
{
    private int $tries = 0;
    private string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
        if (!file_exists($this->file)) {
            $defaultUsers = [
                [
                    'username' => 'John1',
                    'password' => md5('p@55w0RD')
                ],
                [
                    'username' => 'User23',
                    'password' => md5('123456')
                ],
                [
                    'username' => 'qwerty',
                    'password' => md5('pass')
                ]
            ];
            file_put_contents($this->file, json_encode(['users' => $defaultUsers], JSON_PRETTY_PRINT));
        }
    }

    private function loadUsers(): array
    {
        $data = json_decode(file_get_contents($this->file), true);
        return $data['users'] ?? [];
    }

    public function authorize(): ?string
    {
        $users = $this->loadUsers();
        $userName = "";
        while ($this->tries < 3) {
            $password = readline("Enter password: ");
            $userFound = false;
            foreach ($users as $user) {
                if (md5($password) === $user['password']) {
                    echo "Welcome {$user['username']}" . PHP_EOL;
                    $userName = $user['username'];
                    $userFound = true;
                    break;
                }
            }
            if ($userFound) {
                return $userName;
            } else {
                echo "Incorrect password " . PHP_EOL;
                $this->tries++;
            }
            if ($this->tries === 3) {
                exit("Too many tries");
            }
        }
        return null;
    }
}