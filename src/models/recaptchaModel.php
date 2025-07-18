<?php 
namespace App\models;

class RecaptchaModel extends AbstractModel
{
   private string $secretKey;

    public function __construct(string $secretKey)
    {
        parent::__construct();
        $this->secretKey = $secretKey;
    }

    public function verifyRecaptcha(string $recaptchaResponse, string $remoteIp): array
    {
        if (empty($recaptchaResponse)) {
            return ['success' => false, 'error' => 'reCAPTCHA n\'a pas été soumis.'];
        }

        $baseUrl = "https://www.google.com/recaptcha/api/siteverify";
        $query = "secret={$this->secretKey}&response={$recaptchaResponse}";
        $url = $baseUrl . '?' . $query;

        $response = file_get_contents("$url&remoteip=$remoteIp");

        $responseKeys = json_decode($response, true);

        if (intval($responseKeys["success"]) !== 1) {
            return ['success' => false, 'error' => 'Veuillez prouver que vous n\'êtes pas un robot.'];
        }

        return ['success' => true];
    }
}