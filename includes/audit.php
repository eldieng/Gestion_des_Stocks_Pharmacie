<?php
// Fonction utilitaire pour enregistrer une action dans l'audit log
function log_action($pdo, $user_id, $action, $cible, $cible_id = null, $details = null) {
    $stmt = $pdo->prepare('INSERT INTO audit_log (utilisateur_id, action, table_cible, element_id, details) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$user_id, $action, $cible, $cible_id, $details]);
}
?>
