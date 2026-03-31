# Admin API Documentation

This document covers the API endpoints available for the Admin role in the Payground application.

## Base Configuration

- **Base URL:** `{{base_url}}/api/admin`
- **Authentication:** Bearer Token (Laravel Sanctum)
- **Role Requirement:** `admin`

---

## 1. Dashboard

### Get Dashboard Statistics
Returns a summary of application data and statistics.

- **URL:** `/dashboard`
- **Method:** `GET`
- **Success Response:**
    - **Code:** 200 OK
    - **Content:**
      ```json
      {
          "stats": {
              "total_users": 150,
              "total_owners": 20,
              "total_customers": 130,
              "total_turfs": 25,
              "total_bookings": 500,
              "pending_payouts": 5,
              "total_revenue": 50000.00,
              "admin_revenue": 5000.00,
              "commission_rate": 10,
              "razorpay_key": "rzp_test_..."
          }
      }
      ```

---

## 2. Grounds Management

### List All Grounds
Retrieve a paginated list of all registered turfs/grounds.

- **URL:** `/grounds`
- **Method:** `GET`
- **Query Parameters:**
    - `page` (optional): Page number.
- **Success Response:**
    - **Code:** 200 OK
    - **Content:** Standard Laravel pagination object containing turf records with `owner` and `slots`.

### Update Ground (Moderation)
Update details or status of a specific ground.

- **URL:** `/grounds/{id}`
- **Method:** `PUT`
- **Body Parameters:**
    - `status` (optional): `active` | `inactive`
    - `name` (optional): String
- **Success Response:**
    - **Code:** 200 OK
    - **Content:**
      ```json
      {
          "message": "Turf updated by admin",
          "turf": { ... }
      }
      ```

---

## 3. Payout Management

### List Payout Requests
Retrieve a paginated list of payout requests from owners.

- **URL:** `/payouts`
- **Method:** `GET`
- **Query Parameters:**
    - `status` (optional): `pending` | `approved` | `rejected` | `declined`
- **Success Response:**
    - **Code:** 200 OK

### Process Payout Request
Approve or reject a payout request.

- **URL:** `/payouts/{id}`
- **Method:** `POST`
- **Body Parameters:**
    - `status` (required): `approved` | `rejected` | `declined`
    - `admin_notes` (optional): String
- **Success Response:**
    - **Code:** 200 OK
    - **Content:**
      ```json
      {
          "message": "Payout status updated",
          "payout": { ... }
      }
      ```

---

## 4. User Management

### List Users
Retrieve a paginated list of all users.

- **URL:** `/users`
- **Method:** `GET`
- **Query Parameters:**
    - `role` (optional): `admin` | `owner` | `customer`
- **Success Response:**
    - **Code:** 200 OK

### Create User
Create a new user manually.

- **URL:** `/users`
- **Method:** `POST`
- **Body Parameters:**
    - `name` (required)
    - `email` (required, unique)
    - `phone` (optional, unique)
    - `password` (required, min 8 chars)
    - `role` (required): `admin` | `owner` | `customer`
    - `city` (optional)
    - `venue_name` (optional, for owners)
    - `venue_address` (optional, for owners)
- **Success Response:**
    - **Code:** 201 Created

### Get User Details
- **URL:** `/users/{id}`
- **Method:** `GET`

### Update User
- **URL:** `/users/{id}`
- **Method:** `PUT`
- **Body Parameters:** Same as create (all optional).

### Delete User
- **URL:** `/users/{id}`
- **Method:** `DELETE`

### Update Owner Payment Keys
Special endpoint to update Razorpay credentials for a specific owner.

- **URL:** `/users/{id}/payment-keys`
- **Method:** `PUT`
- **Body Parameters:**
    - `razorpay_key` (required)
    - `razorpay_secret` (required)
- **Success Response:**
    - **Code:** 200 OK

---

## 5. Global Settings

### Get Application Settings
Retrieve global configuration like commission rates and Razorpay modes.

- **URL:** `/settings`
- **Method:** `GET`
- **Success Response:**
    - **Code:** 200 OK
    - **Content:**
      ```json
      {
          "settings": {
              "admin_commission": 10,
              "razorpay_mode": "test",
              "razorpay_key": "...",
              "razorpay_secret": "..."
          }
      }
      ```

### Update Application Settings
- **URL:** `/settings`
- **Method:** `PUT`
- **Body Parameters:**
    - `admin_commission` (optional, 0-100)
    - `razorpay_mode` (optional, `test` | `live`)
    - `razorpay_key` (optional)
    - `razorpay_secret` (optional)
- **Success Response:**
    - **Code:** 200 OK
