<?php

class NoestAPI {
    private $apiToken;
    private $userGuid;
    private $baseUrl = 'https://app.noest-dz.com/api/public';
    
    public function __construct($apiToken, $userGuid) {
        $this->apiToken = $apiToken;
        $this->userGuid = $userGuid;
    }
    
    /**
     * Make API request to Noest
     */
    private function makeRequest($endpoint, $data = null, $method = 'POST') {
        $url = $this->baseUrl . $endpoint;
        
        // Add API credentials to data
        if ($data === null) {
            $data = [];
        }
        $data['api_token'] = $this->apiToken;
        $data['user_guid'] = $this->userGuid;
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            // For POST requests, check if data contains arrays that need special handling
            if (isset($data['trackings']) && is_array($data['trackings'])) {
                // For tracking requests, send as JSON
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        } elseif ($method === 'GET') {
            if ($data) {
                $url .= '?' . http_build_query($data);
                curl_setopt($ch, CURLOPT_URL, $url);
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            throw new Exception('API Error: HTTP ' . $httpCode . ' - ' . $response);
        }
        
        return $decodedResponse;
    }
    
    /**
     * Create a new delivery order
     */
    public function createDeliveryOrder($orderData) {
        $data = [
            'reference' => $orderData['reference'] ?? '',
            'client' => $orderData['client'],
            'phone' => $orderData['phone'],
            'phone_2' => $orderData['phone_2'] ?? '',
            'adresse' => $orderData['adresse'],
            'wilaya_id' => $orderData['wilaya_id'],
            'commune' => $orderData['commune'],
            'montant' => $orderData['montant'],
            'remarque' => $orderData['remarque'] ?? '',
            'produit' => $orderData['produit'],
            'type_id' => $orderData['type_id'] ?? 1, // 1 = Livraison
            'poids' => $orderData['poids'] ?? 1,
            'stop_desk' => $orderData['stop_desk'] ?? 0, // 0 = domicile, 1 = stop desk
            'station_code' => $orderData['station_code'] ?? '',
            'stock' => $orderData['stock'] ?? 0,
            'quantite' => $orderData['quantite'] ?? '',
            'can_open' => $orderData['can_open'] ?? 1
        ];
        
        return $this->makeRequest('/create/order', $data, 'POST');
    }
    
    /**
     * Get delivery fees
     */
    public function getDeliveryFees() {
        return $this->makeRequest('/fees', [], 'GET');
    }
    
    /**
     * Get list of stations/desks
     */
    public function getStations() {
        return $this->makeRequest('/desks', [], 'GET');
    }
    
    /**
     * Get multiple order tracking info
     */
    public function getMultipleTracking($trackingNumbers) {
        $data = [
            'trackings' => $trackingNumbers
        ];
        
        return $this->makeRequest('/get/trackings/info', $data, 'POST');
    }
    
    /**
     * Get single tracking information
     */
    public function getTracking($trackingNumber) {
        $result = $this->getMultipleTracking([$trackingNumber]);
        return $result[$trackingNumber] ?? null;
    }
    
    /**
     * Validate an order
     */
    public function validateOrder($tracking) {
        $data = [
            'tracking' => $tracking
        ];
        
        return $this->makeRequest('/valid/order', $data, 'POST');
    }
    
    /**
     * Delete an order
     */
    public function deleteOrder($tracking) {
        $data = [
            'tracking' => $tracking
        ];
        
        return $this->makeRequest('/delete/order', $data, 'POST');
    }
    
    /**
     * Update an order
     */
    public function updateOrder($tracking, $updateData) {
        $data = array_merge(['tracking' => $tracking], $updateData);
        
        return $this->makeRequest('/update/order', $data, 'POST');
    }
    
    /**
     * Add a remark to an order
     */
    public function addRemark($tracking, $content) {
        $data = [
            'tracking' => $tracking,
            'content' => $content
        ];
        
        return $this->makeRequest('/add/maj', $data, 'GET');
    }
    
    /**
     * Request a new delivery attempt
     */
    public function requestNewAttempt($tracking) {
        $data = [
            'tracking' => $tracking
        ];
        
        return $this->makeRequest('/ask/new-tentative', $data, 'GET');
    }
    
    /**
     * Request a return
     */
    public function requestReturn($tracking) {
        $data = [
            'tracking' => $tracking
        ];
        
        return $this->makeRequest('/ask/return', $data, 'GET');
    }
    
    /**
     * Add a product to Noest stock
     */
    public function addProductToStock($productData) {
        $data = [
            'reference' => $productData['reference'],
            'name' => $productData['name'],
            'price' => $productData['price'],
            'description' => $productData['description'] ?? '',
            'category' => $productData['category'] ?? '',
            'weight' => $productData['weight'] ?? 1,
            'quantity' => $productData['quantity'] ?? 1
        ];
        
        return $this->makeRequest('/add/product', $data, 'POST');
    }
    
    /**
     * Update product stock quantity
     */
    public function updateProductStock($reference, $quantity) {
        $data = [
            'reference' => $reference,
            'quantity' => $quantity
        ];
        
        return $this->makeRequest('/update/product/stock', $data, 'POST');
    }
    
    /**
     * Get product information from stock
     */
    public function getProduct($reference) {
        $data = [
            'reference' => $reference
        ];
        
        return $this->makeRequest('/get/product', $data, 'GET');
    }
    
    /**
     * Get all products in stock
     */
    public function getAllProducts() {
        return $this->makeRequest('/get/products', [], 'GET');
    }
    
    /**
     * Delete a product from stock
     */
    public function deleteProduct($reference) {
        $data = [
            'reference' => $reference
        ];
        
        return $this->makeRequest('/delete/product', $data, 'POST');
    }

    /**
     * Get communes (districts) for a specific wilaya
     */
    public function getCommunes($wilayaId) {
        $data = [
            'wilaya_id' => $wilayaId
        ];
        
        return $this->makeRequest('/get/communes', $data, 'GET');
    }

    /**
     * Download order label
     */
    public function downloadOrderLabel($tracking) {
        $data = [
            'tracking' => $tracking
        ];
        
        return $this->makeRequest('/get/order/label', $data, 'GET');
    }
}
