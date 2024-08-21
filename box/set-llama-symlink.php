<?php

$TARGET='/home/box/llama-server';

// Function to check if the CPU supports AVX2 instructions
function has_avx2_support(): bool
{
    $cpuinfo = file_get_contents('/proc/cpuinfo');
    return strpos($cpuinfo, 'avx2') !== false;
}

// Check if the system supports AVX2 instructions
$avx2_supported = has_avx2_support();

// Set the symlink target based on AVX2 support
$symlink_target = $avx2_supported ? $TARGET.',avx2' : $TARGET.',generic';

// Remove the existing symlink if it exists
if (file_exists($TARGET)) {
    unlink($TARGET);
}

// Create the new symlink
symlink($symlink_target, $TARGET);

echo "Symlink set to: $symlink_target\n";

?>
