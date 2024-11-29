<?php
// Inizia la sessione per utilizzare le variabili di sessione
add_action('init', 'astrologer_wp_start_session', 1);
function astrologer_wp_start_session() {
    if (!session_id()) {
        session_start();
    }
}

// Crea uno shortcode per mostrare il div.
add_shortcode('astrologer_wp', 'astrologer_wp_shortcode');
function astrologer_wp_shortcode() {
    $name = get_option('astrologer_wp_name');
    $date = get_option('astrologer_wp_date');
    $astrologerApiKey = get_option('astrologer_wp__api_key');
    $wheelOnly = get_option('astrologer_wp__wheel_only_chart');
    $time = '';
    $astrologerWpYear = '1994';
    $custom_value = '';
    $error_message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_time') {
        if (empty($_POST['customValue']) || empty($_POST['astrologerWpYear'])) {
            $error_message = 'Il campo valore personalizzato è obbligatorio.';
        } else {
            $custom_value = sanitize_text_field($_POST['customValue']);
            $astrologerWpYear = sanitize_text_field($_POST['astrologerWpYear']);
            $data = astrologer_wp__get_birth_chart($astrologerApiKey, $astrologerWpYear, $wheelOnly);
            $time = $data['chart'];
            // Salva l'ora corrente in una variabile di sessione per visualizzarla dopo il redirect
            $_SESSION['current_time'] = $time;
            $_SESSION['custom_value'] = $custom_value;
            $_SESSION['astrologerWpYear'] = $astrologerWpYear;
            // Reindirizza l'utente alla stessa pagina per visualizzare l'aggiornamento
            wp_redirect($_SERVER['REQUEST_URI']);
            exit;
        }
    }

    // Recupera l'ora corrente dalla sessione se disponibile
    if (isset($_SESSION['current_time'])) {
        $time = $_SESSION['current_time'];
        // Rimuovi l'ora corrente dalla sessione solo se è stata visualizzata
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            unset($_SESSION['current_time']);
        }
    }

    // Recupera il valore personalizzato dalla sessione se disponibile
    if (isset($_SESSION['custom_value'])) {
        $custom_value = $_SESSION['custom_value'];
        // Rimuovi il valore personalizzato dalla sessione solo se è stato visualizzato
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            unset($_SESSION['custom_value']);
        }
    }

    // Recupera l'anno dalla sessione se disponibile
    if (isset($_SESSION['astrologerWpYear'])) {
        $astrologerWpYear = $_SESSION['astrologerWpYear'];
        // Rimuovi l'anno dalla sessione solo se è stato visualizzato
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            unset($_SESSION['astrologerWpYear']);
        }
    }

    ob_start();
?>
    <div id="myPlugin">
        <?php if (!empty($time)): ?>
            <object type="image/svg+xml" data="data:image/svg+xml;base64,<?php echo base64_encode($time); ?>"></object>
        <?php endif; ?>
        <form id="myPluginForm" method="post">
            <input type="hidden" name="action" value="update_time">
            <input type="text" name="customValue" placeholder="Inserisci un valore personalizzato" value="test" required>
            <input type="text" name="astrologerWpYear" placeholder="Inserisci l'anno" required value="<?php echo $astrologerWpYear; ?>">
            <button type="submit">Aggiorna Ora</button>
        </form>
    </div>
<?php

    return ob_get_clean();
}
