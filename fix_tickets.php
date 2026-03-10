<?php
$file = 'c:\\Users\\Walid\\Herd\\helpdesk-system\\app\\Livewire\\Dashboard\\TicketsTable.php';
$content = file_get_contents($file);

$search = <<<EOT
                // Show tickets from their specialty category OR assigned to them
                \$query->where(function (\$q) use (\$user) {
                    \$q->where('category_id', \$user->specialty_id)
                        ->orWhere('assigned_to', \$user->id);
                });
EOT;

$replace = <<<EOT
                // Show tickets from their specialty category that are UNASSIGNED, OR tickets assigned to them
                \$query->where(function (\$q) use (\$user) {
                    \$q->where(function (\$subQ) use (\$user) {
                        \$subQ->where('category_id', \$user->specialty_id)
                             ->whereNull('assigned_to');
                    })->orWhere('assigned_to', \$user->id);
                });
EOT;

if (strpos($content, "whereNull('assigned_to')") !== false) {
    echo "Already contains fix!\n";
}
else {
    $newContent = str_replace($search, $replace, $content);
    if ($newContent !== $content) {
        file_put_contents($file, $newContent);
        echo "Successfully updated TicketsTable.php\n";
    }
    else {
        echo "Could not find target string to replace.\n";
    }
}
