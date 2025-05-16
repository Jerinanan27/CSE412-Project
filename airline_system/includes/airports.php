<?php
// Array of dummy airport data
$airports = [
    [
        'code' => 'JFK',
        'name' => 'John F. Kennedy International Airport',
        'city' => 'New York',
        'country' => 'USA'
    ],
    [
        'code' => 'LAX',
        'name' => 'Los Angeles International Airport',
        'city' => 'Los Angeles',
        'country' => 'USA'
    ],
    [
        'code' => 'LHR',
        'name' => 'Heathrow Airport',
        'city' => 'London',
        'country' => 'UK'
    ],
    [
        'code' => 'NRT',
        'name' => 'Narita International Airport',
        'city' => 'Tokyo',
        'country' => 'Japan'
    ],
    [
        'code' => 'SYD',
        'name' => 'Sydney Kingsford Smith Airport',
        'city' => 'Sydney',
        'country' => 'Australia'
    ],
    [
        'code' => 'CDG',
        'name' => 'Charles de Gaulle Airport',
        'city' => 'Paris',
        'country' => 'France'
    ],
    [
        'code' => 'DXB',
        'name' => 'Dubai International Airport',
        'city' => 'Dubai',
        'country' => 'UAE'
    ],
    [
        'code' => 'SIN',
        'name' => 'Changi Airport',
        'city' => 'Singapore',
        'country' => 'Singapore'
    ],
    [
        'code' => 'DAC',
        'name' => 'Dhaka International Airport',
        'city' => 'Dhaka',
        'country' => 'Bangladesh'
    ],
    [
        'code' => 'CTG',
        'name' => 'Chittagong International Airport',
        'city' => 'Chittagong',
        'country' => 'Bangladesh'
    ],

];

// Function to get airport by code
function getAirportByCode($code) {
    global $airports;
    foreach ($airports as $airport) {
        if ($airport['code'] === $code) {
            return $airport;
        }
    }
    return null;
}

// Function to get all airports
function getAllAirports() {
    global $airports;
    return $airports;
}
?>