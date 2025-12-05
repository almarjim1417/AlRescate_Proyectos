<?php
// --------------------------------------------------------------------
// HERRAMIENTAS DEL PORTAL (ENCRIPTACIÓN)
// --------------------------------------------------------------------

// CLAVE SECRETA: Cambia esto por una frase larga y única para tu instalación
define('PORTAL_KEY', 'Clave_Secreta_Fincas_2025_Super_Segura!');
define('PORTAL_CIPHER', 'aes-256-cbc');

/**
 * Encripta un ID para ponerlo en la URL
 */
function portal_encrypt($data) {
    $key = hash('sha256', PORTAL_KEY);
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(PORTAL_CIPHER));
    $encrypted = openssl_encrypt($data, PORTAL_CIPHER, $key, 0, $iv);
    // Devolvemos base64 seguro para URL (cambiamos + por - y / por _)
    $output = base64_encode($encrypted . '::' . $iv);
    return str_replace(array('+', '/'), array('-', '_'), $output);
}

/**
 * Desencripta el token de la URL para obtener el ID original
 */
function portal_decrypt($data) {
    // Revertimos el cambio de URL safe
    $data = str_replace(array('-', '_'), array('+', '/'), $data);
    $data = base64_decode($data);
    
    if (strpos($data, '::') === false) return 0; // Formato inválido
    
    list($encrypted_data, $iv) = explode('::', $data, 2);
    $key = hash('sha256', PORTAL_KEY);
    return openssl_decrypt($encrypted_data, PORTAL_CIPHER, $key, 0, $iv);
}
?>