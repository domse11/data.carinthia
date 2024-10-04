<?php
/**
 * Plugin Name: Event API Display
 * Description: Fetches and displays event data from the dataCycle API.
 */
 
function fetch_event_api_data() {
    $api_url = "https://data.carinthia.com/api/v4/endpoints/557ea81f-6d65-6476-9e01-d196112514d2?include=image&token=9962098a5f6c6ae8d16ad5aba95afee0";
    
    $response = file_get_contents($api_url);
    
    $data = json_decode($response, true);

    if ($data && isset($data['@graph'])) {
        ob_start();
        echo "<div class='event-section'>";
        echo "<div class='event-list'>";

        foreach ($data['@graph'] as $index => $event) {
            echo "<div class='event-item'>";
            echo "<div class='event-header'>";
            echo "<strong>" . htmlspecialchars($event['name']) . "</strong>";
            echo "</div>";

            $startDate = new DateTime($event['startDate']);
            $endDate = new DateTime($event['endDate']);
            echo "<div><strong>Datum:</strong> " . $startDate->format('d.m.Y') . " bis " . $endDate->format('d.m.Y') . "</div>";

            $description = strip_tags($event['description']);
            $description = preg_replace('/([.,;:])([A-Za-z])/', '$1 $2', $description);
            $description = preg_replace('/(SCHAUSPIEL:|REGIE:|FOTO:|SPIELORT:|TERMINE:|BEGINN:|TICKETS:|EINTRITT:)/', "\n$1", $description);
            $description = preg_replace('/(Tel\.:|info@|www\.)/', "\n$1", $description);

            $short_description = mb_substr($description, 0, 200) . '...';

            echo "<div class='event-short-description' id='short-description-" . $index . "'>";
            echo "<strong>Beschreibung:</strong> " . nl2br(htmlspecialchars($short_description)) . "<br>";
            echo "</div>";

            echo "<div class='event-details' id='full-description-" . $index . "' style='display:none;'>";
            echo "<strong>Beschreibung:</strong> " . nl2br(htmlspecialchars($description)) . "<br>";

            if (!empty($event['image'])) {
                echo "<div class='event-image'>";
                foreach ($event['image'] as $image) {
                    echo "<img src='" . htmlspecialchars($image['dc:originalUrl']) . "' alt='" . htmlspecialchars($image['name']) . "'>";
                }
                echo "</div>";
            }

            echo "</div>";

            echo "<button class='toggle-button' data-index='" . $index . "'>Mehr anzeigen</button>";
            echo "</div>";
        }

        echo "</div>";
        echo "</div>";

        return ob_get_clean();
    } else {
        return "Keine Daten gefunden.";
    }
}

function register_event_api_shortcode() {
    add_shortcode('event_api', 'fetch_event_api_data');
}

add_action('init', 'register_event_api_shortcode');

function add_event_api_scripts() {
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var toggleButtons = document.querySelectorAll(".toggle-button");

            toggleButtons.forEach(function(button) {
                button.addEventListener("click", function() {
                    var index = this.getAttribute("data-index");
                    var shortDescription = document.getElementById("short-description-" + index);
                    var fullDescription = document.getElementById("full-description-" + index);

                    if (fullDescription.style.display === "none") {
                        fullDescription.style.display = "block";
                        shortDescription.style.display = "none";
                        this.textContent = "Weniger anzeigen";
                    } else {
                        fullDescription.style.display = "none";
                        shortDescription.style.display = "block";
                        this.textContent = "Mehr anzeigen";
                    }
                });
            });
        });
    </script>';
}

add_action('wp_head', 'add_event_api_scripts');
?>
