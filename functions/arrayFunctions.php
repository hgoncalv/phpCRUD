<?php
function createAssociativeArray($flatArray)
{
    $result = [];
    $count = count($flatArray);

    // Ensure the array has an even number of elements
    if ($count % 2 !== 0) {
        return false; // Return false if the array is not even
    }

    // Iterate over the array and create key-value pairs
    for ($i = 0; $i < $count; $i += 2) {
        $result[$flatArray[$i]] = $flatArray[$i + 1];
    }

    return $result;
}
