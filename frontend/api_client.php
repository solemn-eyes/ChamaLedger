<?php

class ApiClient {
    private $baseUrl = 'http://localhost:5000/api';

    private function request($endpoint, $method = 'GET', $data = []) {
        $ch = curl_init();
        $url = $this->baseUrl . $endpoint;
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            // Handle error (for simplicity just returning null or printing)
             return null; 
             // echo 'Error:' . curl_error($ch);
        }
        
        curl_close($ch);
        return json_decode($response, true);
    }

    public function getGroups() {
        return $this->request('/groups');
    }

    public function createGroup($name, $description) {
        return $this->request('/groups', 'POST', ['name' => $name, 'description' => $description]);
    }

    public function getGroupMembers($groupId) {
        return $this->request("/groups/$groupId/members");
    }

    public function addMember($name, $phone, $groupId) {
        return $this->request('/members', 'POST', ['name' => $name, 'phone' => $phone, 'group_id' => $groupId]);
    }

    public function getGroupTransactions($groupId) {
        return $this->request("/groups/$groupId/transactions");
    }
    
    public function getGroupBalance($groupId) {
        return $this->request("/groups/$groupId/balance");
    }

    public function addContribution($memberId, $groupId, $amount, $description = "Contribution") {
        return $this->request('/contributions', 'POST', [
            'member_id' => $memberId,
            'group_id' => $groupId,
            'amount' => $amount,
            'description' => $description
        ]);
    }

    public function initiateMpesaPayment($memberId, $groupId, $phone, $amount) {
        return $this->request('/mpesa/stkpush', 'POST', [
            'member_id' => $memberId,
            'group_id' => $groupId,
            'phone' => $phone,
            'amount' => $amount
        ]);
    }
}
?>
