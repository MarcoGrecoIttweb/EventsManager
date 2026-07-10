<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = \App\Models\Comment::query()->orderByDesc('data')->first();
if (!$c) {
    echo "no comments\n";
    exit(0);
}

$id = $c->getKey();
echo "id={$id}\n";
echo "table=" . $c->getTable() . "\n";
echo "pk=" . $c->getKeyName() . "\n";
echo "exists_before=" . (int) \App\Models\Comment::query()->whereKey($id)->exists() . "\n";

try {
    $res = $c->delete();
    echo "delete_return=" . (int) $res . "\n";
} catch (\Throwable $e) {
    echo "delete_throw=" . $e->getMessage() . "\n";
}

echo "exists_after=" . (int) \App\Models\Comment::query()->whereKey($id)->exists() . "\n";

