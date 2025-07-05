<?php
// Delivery fees configuration based on tarif.txt
class DeliveryFees {
    private static $fees = [
        1 => ['name' => 'Adrar', 'domicile' => 1500, 'stopdesk' => 1000],
        2 => ['name' => 'Chlef', 'domicile' => 800, 'stopdesk' => 500],
        3 => ['name' => 'Laghouat', 'domicile' => 1000, 'stopdesk' => 600],
        4 => ['name' => 'Oum El Bouaghi', 'domicile' => 800, 'stopdesk' => 500],
        5 => ['name' => 'Batna', 'domicile' => 800, 'stopdesk' => 500],
        6 => ['name' => 'Béjaïa', 'domicile' => 800, 'stopdesk' => 500],
        7 => ['name' => 'Biskra', 'domicile' => 1000, 'stopdesk' => 600],
        8 => ['name' => 'Béchar', 'domicile' => 1200, 'stopdesk' => 800],
        9 => ['name' => 'Blida', 'domicile' => 600, 'stopdesk' => 400],
        10 => ['name' => 'Bouira', 'domicile' => 700, 'stopdesk' => 450],
        11 => ['name' => 'Tamanrasset', 'domicile' => 2000, 'stopdesk' => 1500],
        12 => ['name' => 'Tébessa', 'domicile' => 900, 'stopdesk' => 600],
        13 => ['name' => 'Tlemcen', 'domicile' => 800, 'stopdesk' => 500],
        14 => ['name' => 'Tiaret', 'domicile' => 900, 'stopdesk' => 600],
        15 => ['name' => 'Tizi Ouzou', 'domicile' => 700, 'stopdesk' => 450],
        16 => ['name' => 'Alger', 'domicile' => 600, 'stopdesk' => 400],
        17 => ['name' => 'Djelfa', 'domicile' => 1000, 'stopdesk' => 600],
        18 => ['name' => 'Jijel', 'domicile' => 800, 'stopdesk' => 500],
        19 => ['name' => 'Sétif', 'domicile' => 800, 'stopdesk' => 500],
        20 => ['name' => 'Saïda', 'domicile' => 900, 'stopdesk' => 600],
        21 => ['name' => 'Skikda', 'domicile' => 800, 'stopdesk' => 500],
        22 => ['name' => 'Sidi Bel Abbès', 'domicile' => 800, 'stopdesk' => 500],
        23 => ['name' => 'Annaba', 'domicile' => 800, 'stopdesk' => 500],
        24 => ['name' => 'Guelma', 'domicile' => 900, 'stopdesk' => 600],
        25 => ['name' => 'Constantine', 'domicile' => 800, 'stopdesk' => 500],
        26 => ['name' => 'Médéa', 'domicile' => 700, 'stopdesk' => 450],
        27 => ['name' => 'Mostaganem', 'domicile' => 800, 'stopdesk' => 500],
        28 => ['name' => 'M\'Sila', 'domicile' => 800, 'stopdesk' => 500],
        29 => ['name' => 'Mascara', 'domicile' => 800, 'stopdesk' => 500],
        30 => ['name' => 'Ouargla', 'domicile' => 1100, 'stopdesk' => 700],
        31 => ['name' => 'Oran', 'domicile' => 800, 'stopdesk' => 500],
        32 => ['name' => 'El Bayadh', 'domicile' => 1200, 'stopdesk' => 800],
        33 => ['name' => 'Illizi', 'domicile' => 1900, 'stopdesk' => 1500],
        34 => ['name' => 'Bordj Bou Arreridj', 'domicile' => 800, 'stopdesk' => 500],
        35 => ['name' => 'Boumerdès', 'domicile' => 500, 'stopdesk' => 300],
        36 => ['name' => 'El Tarf', 'domicile' => 900, 'stopdesk' => 600],
        37 => ['name' => 'Tindouf', 'domicile' => 1700, 'stopdesk' => 1000],
        38 => ['name' => 'Tissemsilt', 'domicile' => 800, 'stopdesk' => 500],
        39 => ['name' => 'El Oued', 'domicile' => 1100, 'stopdesk' => 700],
        40 => ['name' => 'Khenchela', 'domicile' => 900, 'stopdesk' => 600],
        41 => ['name' => 'Souk Ahras', 'domicile' => 900, 'stopdesk' => 600],
        42 => ['name' => 'Tipaza', 'domicile' => 600, 'stopdesk' => 400],
        43 => ['name' => 'Mila', 'domicile' => 800, 'stopdesk' => 500],
        44 => ['name' => 'Aïn Defla', 'domicile' => 800, 'stopdesk' => 500],
        45 => ['name' => 'Naâma', 'domicile' => 1200, 'stopdesk' => 800],
        46 => ['name' => 'Aïn Témouchent', 'domicile' => 800, 'stopdesk' => 500],
        47 => ['name' => 'Ghardaïa', 'domicile' => 1100, 'stopdesk' => 700],
        48 => ['name' => 'Relizane', 'domicile' => 800, 'stopdesk' => 500],
        49 => ['name' => 'Timimoun', 'domicile' => 1500, 'stopdesk' => 1000],
        51 => ['name' => 'Ouled Djellal', 'domicile' => 1000, 'stopdesk' => 600],
        52 => ['name' => 'Beni Abbes', 'domicile' => 1200, 'stopdesk' => 800],
        53 => ['name' => 'In Salah', 'domicile' => 1800, 'stopdesk' => 1200],
        55 => ['name' => 'Touggourt', 'domicile' => 1100, 'stopdesk' => 700],
        57 => ['name' => 'El M\'Ghair', 'domicile' => 1100, 'stopdesk' => 700],
        58 => ['name' => 'El Meniaa', 'domicile' => 1100, 'stopdesk' => 800],
    ];
    
    public static function getFeeByWilayaName($wilayaName, $deliveryType = 'domicile') {
        foreach (self::$fees as $wilayaId => $data) {
            if (stripos($data['name'], $wilayaName) !== false || stripos($wilayaName, $data['name']) !== false) {
                return $data[$deliveryType] ?? 0;
            }
        }
        return 0; // Default fee if not found
    }
    
    public static function getFeeByWilayaId($wilayaId, $deliveryType = 'domicile') {
        return self::$fees[$wilayaId][$deliveryType] ?? 0;
    }
    
    public static function getWilayaNameById($wilayaId) {
        return self::$fees[$wilayaId]['name'] ?? '';
    }
    
    public static function getAllWilayas() {
        return self::$fees;
    }
    
    public static function getWilayaIdByName($wilayaName) {
        foreach (self::$fees as $wilayaId => $data) {
            if (stripos($data['name'], $wilayaName) !== false || stripos($wilayaName, $data['name']) !== false) {
                return $wilayaId;
            }
        }
        return 16; // Default to Alger if not found
    }
}
?>
