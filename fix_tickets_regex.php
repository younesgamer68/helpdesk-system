<?php
$file = 'c:\\Users\\Walid\\Herd\\helpdesk-system\\app\\Livewire\\Dashboard\\TicketsTable.php';
$content = file_get_contents($file);

// We replace the old block with the new block using a regex to ignore exact whitespace differences
$pattern = '/\/\/ Show tickets from their specialty category OR assigned to them\s+\$query->where\(function \(\$q\) use \(\$user\) \{\s+\$q->where\(\'category_id\', \$user->specialty_id\)\s+->orWhere\(\'assigned_to\', \$user->id\);\s+\}\);/m';

$replace = <<<EOP
// Show tickets from their specialty category that are UNASSIGNED, OR tickets assigned to them
                \$query->where(function (\$q) use (\$user) {
                    \$q->where(function (\$subQ) use (\$user) {
                        \$subQ->where('category_id', \$user->specialty_id)
                             ->whereNull('assigned_to');
                    })->orWhere('assigned_to', \$user->id);
                });
EOP;

$newContent = preg_replace($pattern, $replace, $content);

if ($newContent !== $content) {
    file_put_contents($file, $newContent);
    echo "Successfully updated TicketsTable.php\n";
    opcache_reset();
}
else {
    echo "Regex did not match anything.\n";
}
