<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Session;

class ChatbotController extends Controller
{
    public function index()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (isset($_GET['user-input'])) {
                $userInput = $_GET['user-input'];
                $botResponse = $this->getBotResponse($userInput);

                // Store the conversation history in session storage
                if (!Session::has('conversation')) {
                    Session::put('conversation', []);
                }

                // Add the user message and bot response to the conversation
                Session::push('conversation', ['role' => 'User', 'content' => $userInput]);
                Session::push('conversation', ['role' => 'Bot', 'content' => $botResponse]);
            }
        }
        return view('homepage');
    }

    public function getBotResponse($message) {
        // Convert the user's input to lowercase for easier comparison
        $lowercaseMessage = strtolower($message);

        // Load the JSON data from the file
        $jsonData = file_get_contents('holiday_data.json');
        $hotels = json_decode($jsonData, true);

        // Check for specific keywords in the user's input and provide corresponding responses
        if (strpos($lowercaseMessage, "holiday") !== false) {
            return "Sure, I can help you with that. What type of holiday are you interested in?";
        } elseif (strpos($lowercaseMessage, "city") !== false) {
            // Check if the user mentioned "city"
            if (strpos($lowercaseMessage, "city") !== false) {
                return "Great! City breaks can be a fantastic way to explore different cultures and attractions. Here are some selections that I can recommend: Venice, Porto, Prague, Amsterdam, and Berlin.";
            } else {
                return "For a city break, popular destinations include Paris, New York, or Tokyo. Are you interested in any of these cities?";
            }
        }elseif (strpos($lowercaseMessage, "adventure") !== false) {
            // Check if the user mentioned "adventure"
            if (strpos($lowercaseMessage, "adventure") !== false) {
                return "Awesome! Adventure holidays offer thrilling experiences and opportunities for exploration. Some popular adventure destinations are Costa Rica, New Zealand, Nepal, and South Africa.";
            } else {
                return "If you're looking for an adventurous getaway, I can suggest destinations like Costa Rica, New Zealand, Nepal, and South Africa. Are any of these places on your radar?";
            }
        } elseif (strpos($lowercaseMessage, "continent") !== false) {
            // Extract the continent mentioned by the user
            $continent = $this->extractParameter($lowercaseMessage, "continent");

            if ($continent) {
                return "Considering a city break in $continent? That's a great choice! What is your budget for this trip?";
            } else {
                return "Which continent are you planning to visit for your city break?";
            }
        } elseif (strpos($lowercaseMessage, "price") !== false) {
            // Extract the price range mentioned by the user
            $priceRange = $this->extractParameter($lowercaseMessage, "price");

            if ($priceRange) {
                // Filter the hotels based on the price range
                $filteredHotels = $this->filterHotelsByPriceRange($hotels, $priceRange);

                if (!empty($filteredHotels)) {
                    $hotelNames = $this->getHotelNames($filteredHotels);
                    $hotelList = implode(", ", $hotelNames);
                    return "For a city break within the price range of $priceRange, I recommend checking out hotels like $hotelList. Do you have a specific hotel in mind?";
                } else {
                    return "I'm sorry, but I couldn't find any hotels within the specified price range. Please try again with a different range.";
                }
            } else {
                return "What is your budget for this city break? This will help me provide you with suitable hotel recommendations.";
            }
        } elseif (strpos($lowercaseMessage, "hotel") !== false) {
            // Extract the hotel name mentioned by the user
            $hotelName = $this->extractParameter($lowercaseMessage, "hotel");

            if ($hotelName) {
                // Check if the hotel exists in the data
                $hotel = $this->findHotelByName($hotels, $hotelName);

                if ($hotel) {
                    return "Excellent choice! $hotelName is a highly recommended hotel for your city break. Enjoy your trip!";
                } else {
                    return "I'm sorry, but I couldn't find any information about $hotelName. Please try again with a different hotel name.";
                }
            } else {
                return "Do you have a specific hotel in mind for your trip?";
            }
        } else {
            // Handle other queries or unrecognized input
            if (strpos($lowercaseMessage, "recommend a hotel") !== false || strpos($lowercaseMessage, "recommendation") !== false) {
                // Provide a general hotel recommendation
                $randomHotel = $this->getRandomHotel($hotels);
                return "Based on your preferences, I recommend the {$randomHotel['HotelName']} hotel. It is located in {$randomHotel['City']}, {$randomHotel['Country']}, and offers a {$randomHotel['StarRating']}-star experience.";
            } else {
                return "I'm sorry, I couldn't understand your query. Can you please rephrase or provide more details?";
            }
        }
    }
    private function getRandomHotel($hotels) {
        $randomIndex = array_rand($hotels); // Gives user a random hotel suggestion
        return $hotels[$randomIndex];
    }

    private function filterHotelsByPriceRange($hotels, $priceRange) {
        $filteredHotels = [];

        foreach ($hotels as $hotel) {
            if ($hotel['PricePerPerNight'] <= $priceRange) {
                $filteredHotels[] = $hotel;
            }
        }

        return $filteredHotels;
    }

    private function getHotelNames($hotels) {
        $hotelNames = [];

        foreach ($hotels as $hotel) {
            $hotelNames[] = $hotel['HotelName'];
        }

        return $hotelNames;
    }

    private function findHotelByName($hotels, $hotelName) {
        foreach ($hotels as $hotel) {
            if (strtolower($hotel['HotelName']) === strtolower($hotelName)) {
                return $hotel;
            }
        }

        return null;
    }
//// Function to display the conversation history
//    private function displayConversationHistory() {
//        global $conversationHistory;
//
//        $output = implode("\n\n", $conversationHistory);
//        return $output;
//    }
//
// Function to extract a specific parameter from the user's message
    private function extractParameter($message, $parameter) {
        $matches = [];
        $pattern = "/$parameter (\w+)/";
        preg_match($pattern, $message, $matches);
        return isset($matches[1]) ? $matches[1] : null;
    }
}


