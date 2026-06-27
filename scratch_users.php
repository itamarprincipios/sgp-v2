<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "USUÁRIOS NO BANCO DE DADOS:\n";
print_r(App\Models\User::all(['id', 'email', 'role', 'name'])->toArray());
