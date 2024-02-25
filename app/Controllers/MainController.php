<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\RestFul\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\MainModel;
use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\SizeModel;
use App\Models\EventBookingModel;
use App\Models\UserModel;
use \Config\Services;
use \Firebase\JWT\JWT;
use App\Models\AuditModel;
use App\Models\OrderModel;
use App\Models\SalesModel;
use App\Models\NotifModel;
use App\Models\ReviewModel;
use App\Models\ServicesModel;




class MainController extends ResourceController
{
    public function index()
    {

    }
    
    public function checkUsername()
    {
        $username = $this->request->getPost('username'); // Get the username from the POST request
    
        $model = new UserModel();
        $user = $model->where('username', $username)->first();
    
        if ($user) {
            return $this->response->setJSON(['exists' => true]); // Username exists
        }
    
        return $this->response->setJSON(['exists' => false]); // Username does not exist
    }
//edit
public function updateItem($id)
{
    $productModel = new ProductModel();
    $existingData = $productModel->find($id);

    if (!$existingData) {
        return $this->respond(['error' => 'Item not found.'], 404);
    }

    // Save the existing stock before updating
    $oldStock = $existingData['stock'];

    $data = [
        'category_id' => $this->request->getVar('edit_category_id') ?? $existingData['category_id'],
        'size_id' => $this->request->getVar('edit_size_id') ?? $existingData['size_id'],
        'prod_name' => $this->request->getVar('edit_prod_name') ?? $existingData['prod_name'],
        'stock' => $this->request->getVar('edit_stock') ?? $existingData['stock'],
        'price' => $this->request->getVar('edit_price') ?? $existingData['price'],
        'unit_price' => $this->request->getVar('edit_unit_price') ?? $existingData['unit_price'],
        'UPC' => $this->request->getVar('edit_UPC') ?? $existingData['UPC'],
        'product_description' => $this->request->getVar('edit_product_description') ?? $existingData['product_description'],
    ];

    // Handle barcode image update if the 'barcode_image' field is provided and changed in the request
    $barcodeImage = $this->request->getVar('barcode_image');
    $existingBarcodeImage = $existingData['barcode_image'];

    if (!empty($barcodeImage) && $barcodeImage !== $existingBarcodeImage) {
        // Decode the base64 barcode image string and save it to the server
        $decodedBarcodeImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $barcodeImage));
        $barcodeImagePath = 'uploads/barcode_' . $id . '.png'; // Assuming PNG format for barcode images

        file_put_contents(ROOTPATH . 'public/' . $barcodeImagePath, $decodedBarcodeImage);
        $data['barcode_image'] = base_url($barcodeImagePath);
    } else {
        // If 'barcode_image' is not provided or unchanged, retain the existing barcode image path
        $data['barcode_image'] = $existingBarcodeImage;
    }

    // Handle image update if the 'edit_image' field is provided and changed in the request
    $editImage = $this->request->getVar('edit_image');
    $existingImage = $existingData['image'];

    if (!empty($editImage) && $editImage !== $existingImage) {
        $base64Image = $editImage;
        
        // Extract the image extension (e.g., jpeg, png)
        $extension = explode('/', mime_content_type($base64Image))[1];
        $imageName = 'updated_image_' . time() . '.' . $extension; // Generate a unique name for the updated image
        $imagePath = 'uploads/' . $imageName; // Define the path to save the updated image

        // Decode the base64 image string and save it to the server
        $decodedImage = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64Image));
        file_put_contents(ROOTPATH . 'public/' . $imagePath, $decodedImage);

        // Save the updated image path within baseURL
        $data['image'] = base_url($imagePath);
    } else {
        // If 'edit_image' is not provided or unchanged, retain the existing image path
        $data['image'] = $existingImage;
    }

    // Update the data in the database including stock
    $productModel->set($data)->where('ID', $id)->update();

    // Save the updated stock
    $updatedData = $productModel->find($id);
    $updatedStock = $updatedData['stock'];

    // Insert into audit table
    $auditModel = new AuditModel();
    $auditData = [
        'image' => $data['image'], // Use the updated image path
        'category_id' => $data['category_id'], // Use updated category_id
        'prod_name' => $data['prod_name'], // Use updated prod_name
        'stock' => $data['stock'], // Use updated stock
        'price' => $data['price'], // Use updated price
        'unit_price' => $data['unit_price'], // Use updated unit_price
        'size_id' => $data['size_id'], // Use updated size_id
        'UPC' => $data['UPC'], // Use updated UPC
        'barcode_image' => $data['barcode_image'], // Use updated barcode image path
        'product_description' => $data['product_description'], // Use updated product_description
        'old_stock' => $oldStock, // Store the old stock in the audit trail
        'product_id' => $id, // Save the ID of the updated record from productlist into product_id in audit

    ];

    $auditModel->insert($auditData);

    return $this->respond(['message' => 'Item updated successfully.'], 200);
}


//save products............................................................................................

public function save()
{
    
    try {
        // Get barcode_image from POST data
        $barcodeImage = $this->request->getPost('barcode_image');

        // Handle barcode image upload
        $barcodeImageName = 'barcode_' . time(); // Generate a unique name for barcode image
        $barcodeImagePath = ROOTPATH . 'public/uploads/' . $barcodeImageName . '.png'; // Define the path to save the barcode image
        
        

        // Decode base64 encoded image and save it
        $barcodeImageBinary = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $barcodeImage));
        file_put_contents($barcodeImagePath, $barcodeImageBinary);

        $image = $this->request->getFile('image');
        $prods = $image->getName();

        $data = [
            'category_id' => $this->request->getPost('category_id'),
            'size_id' => $this->request->getPost('size_id'),
            'prod_name' => $this->request->getPost('prod_name'),
            'stock' => $this->request->getPost('stock'),
            'price' => $this->request->getPost('price'),
            'unit_price' => $this->request->getPost('unit_price'),
            'UPC' => $this->request->getPost('UPC'),
            'product_description' => $this->request->getPost('product_description'),
            'image' => base_url() . $this->handleImageUpload($image, $prods),
            'barcode_image' => base_url() . 'uploads/' . $barcodeImageName . '.png', // Add barcode_image URL to the data array
        ];
        $auditData = [
            'image' => $data['image'],
            'category_id' => $data['category_id'],
            'size_id' => $data['size_id'],
            'prod_name' => $data['prod_name'],
            'stock' => $data['stock'],
            'price' => $data['price'],
            'unit_price' => $data['unit_price'],
            'UPC' => $data['UPC'],
            'barcode_image' => $data['barcode_image'],
            'product_description' => $data['product_description'],
            // Add other necessary fields as needed
        ];

      $productModel = new ProductModel();

        // Save data in the ProductModel and get the inserted ID
        $savedData = $productModel->save($data);
        $insertedProductId = $productModel->getInsertID(); // Get the ID of the inserted record

        // Prepare data for AuditModel with the inserted product ID
        $auditData['product_id'] = $insertedProductId;

        // Save data in the AuditModel
        $auditModel = new AuditModel();
        $auditModel->insert($auditData);

        return $this->respond($savedData, 200);
    } catch (\Exception $e) {
        log_message('error', 'Error saving data:' . $e->getMessage());
        return $this->failServerError('An error occurred while saving the data.');
    }
}


public function handleImageUpload($image, $prods)
{
    $image->move(ROOTPATH . 'public/uploads/', $prods);
    return 'uploads/' . $prods;
}


    public function del()
    {
      $json = $this->request->getJSON();
      $id = $json->id;
      $main = new MainModel();
      $r = $main->delete($id);
      return $this->respond($r, 200);
    }


    public function getData()
    {
      $main = new MainModel();
      $data = $main->findAll();
      return $this->respond($data, 200);
    }
    public function getDatas()
    {
      $produ = new ProductModel();
      $datas = $produ->findAll();
      return $this->respond($datas, 200);
    }
    public function getservice()
    {
      $service = new ServicesModel();
      $data = $service->findAll();
      return $this->respond($data, 200);
    }

    public function getsize(){
    $siz = new SizeModel();
    $data = $siz->findAll();

    $sizes = [];
    foreach ($data as $size) {
      $sizes[] = [
        'size_id' => $size['size_id'],
        'item_size' => $size['item_size']
      ];
  }
  return $this->respond($sizes, 200);
}

  public function savecateg()
  {
    $json = $this->request->getJSON();
    $data = [
      'category_name' => $json->category_name,
    ];
      $cat = new CategoryModel();
      $catd = $cat->save($data);
      return $this->respond($catd, 200);
  }
  public function editcateg()
{
    try {
        $json = $this->request->getJSON();

        // Extracting data from the request
        $category_id = $json->category_id;
        $category_name = $json->category_name;

        // Find the category by ID
        $categoryModel = new CategoryModel();
        $category = $categoryModel->find($category_id);

        if ($category) {
            // Update the category name
            $category['category_name'] = $category_name;
            $categoryModel->update($category_id, $category);

            return $this->respond(['message' => 'Category updated successfully'], 200);
        } else {
            return $this->respond(['message' => 'Category not found'], 404);
        }
    } catch (\Exception $e) {
        // Log the error for debugging
        log_message('error', 'Category update failed: ' . $e->getMessage());
        return $this->respond(['message' => 'An error occurred while updating the category'], 500);
    }
}


  public function getcat()
{
    $cat = new CategoryModel();
    $data = $cat->findAll();

    $categories = []; // Initialize an array to hold formatted categories
    foreach ($data as $category) {
        $categories[] = [
            'id' => $category['id'],
            'category_name' => $category['category_name']
        ];
    }

    return $this->respond($categories, 200);
  }

  public function getevent()
  {
    $event = new EventBookingModel();
    $data = $event->findAll();
    return $this->respond($data, 200);
  }
  public function getitemsales()
  {
    $sale = new SalesModel();
    $data = $sale->findAll();
    return $this->respond($data, 200);
  }
  public function saveBooking()
  {
    $json = $this->request->getJSON();
    $data = [
      'event_title' => $json->event_title,
      'start_date' => $json->start_date,
      'end_date' => $json->end_date,
      'location' => $json->location,
      'event_description' => $json->event_description,
      'name' => $json->name,
      'email' => $json->email,
      'phone' => $json->phone,
      'service' => $json->service,
    ];
      $event = new EventBookingModel();
      $eve = $event->save($data);
      return $this->respond($eve, 200);
  }


//login sign up

public function register()
    {
        $user = new UserModel();
        $token = $this->verification(50);
        $data = [
            'username' => $this->request->getVar('username'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'token' => $token,
            'status' => 'active',
            'role' => 'user',
        ];
        $u = $user->save($data);
        if ($u) {
            return $this->respond(['msg' => 'okay', 'token' => $token]);
        } else {
            return $this->respond(['msg' => 'failed']);
        }
    }

    public function verification($length)
    {
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($str_result), 0, $length);
    }

    public function login()
    {
        $user = new UserModel();
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');
        $data = $user->where('username', $username)->first();
        if ($data) {
            $pass = $data['password'];
            $authenticatePassword = password_verify($password, $pass);
            if ($authenticatePassword) {
                return $this->respond(['msg' => 'okay', 'token' => $data['token'], 'profile_picture' => $data['profile_picture'], 'address' => $data['address'], 'contact' => $data['contact'], 'other_info' => $data['other_info']]);
            } else {
                return $this->respond(['msg' => 'error'], 200);
            }
        }
    }


    
    // get products by category
    public function getProductsByCategory($categoryId)
{
    $productModel = new ProductModel();
    $products = $productModel->where('category_id', $categoryId)->findAll();
    return $this->response->setJSON($products);
}



//get user information
public function getUserData($token)
    {
        $user = new UserModel();
        $data = $user->where('token',$token)->findAll();
        return $this->respond($data, 200);
    }

public function getaudith($productId)
{
    $audithmodel = new AuditModel();
    // Filter records by the passed $productId
    $data = $audithmodel->where('product_id', $productId)->findAll();
    return $this->respond($data, 200);
}

public function getsales($productId)
{
    $salesModel = new SalesModel();
    // Filter records by the passed $productId
    $data = $salesModel->where('product_id', $productId)->findAll();
    return $this->respond($data, 200);
}


   // save order product
   public function placeOrder()
  {
    $json = $this->request->getJSON();
    $data = [
      'image' => $json->image,
      'prod_name' => $json->prod_name,
      'unit_price' => $json->unit_price,
      'size_id' => $json->size_id,
      'quantity' => $json->quantity,
      'address' => $json->address,
      'contact' => $json->contact,
      'other_info' => $json->other_info,
      'customerName' => $json->customerName,
      'product_id' => $json->id,
      'transaction_code' => $json->transaction_code,
      'total' => $json->total,
      'token' => $json->token,
      'status' => $json->status,
      'category_id' => $json->category_id,

    ];
      $ordermodel = new OrderModel();
      $order = $ordermodel->save($data);
      return $this->respond($order, 200);
  }

  public function addtocart()
  {
    $json = $this->request->getJSON();
    $data = [
      'image' => $json->image,
      'prod_name' => $json->prod_name,
      'unit_price' => $json->unit_price,
      'size_id' => $json->size_id,
      'quantity' => $json->quantity,
      'address' => $json->address,
      'contact' => $json->contact,
      'other_info' => $json->other_info,
      'customerName' => $json->customerName,
      'product_id' => $json->id,
      'transaction_code' => $json->transaction_code,
      'total' => $json->total,
      'token' => $json->token,
      'status' => $json->status,

    ];
      $ordermodel = new OrderModel();
      $order = $ordermodel->save($data);
      return $this->respond($order, 200);
  }

  public function getOrder()
  {
    $ordermodel = new OrderModel();
    $data = $ordermodel->findAll();
    return $this->respond($data, 200);
  }

  public function getprod()
  {
    $productModel = new ProductModel();
    $data = $productModel->findAll();
    return $this->respond($data, 200);
  }


// Update order status and save data to sales table
public function updateOrderStatus($id)
{

    $orderModel = new OrderModel();
    $existingOrder = $orderModel->find($id);

    if (!$existingOrder) {
        return $this->respond(['error' => 'Order not found.'], 404);
    }

    // Get the status from the request
    $newStatus = $this->request->getVar('status');
    $reason = $this->request->getVar('reason');
    $newQuantity = $this->request->getVar('quantity'); // Add this line
    $newTotal = $this->request->getVar('total'); // Add this line

    // Validate the status - You can add more validation as needed
    if (!in_array($newStatus, ['approved', 'denied', 'pending', 'delivering', 'recieved', 'cancelled','cancel','pendingbackpricestock'])) {
        return $this->respond(['error' => 'Invalid status.'], 400);
    }

    // If the status is 'approved', update the stock and price based on the quantity
    if ($newStatus === 'approved') {
        // Get the product ID and quantity from the order
        $productId = $existingOrder['product_id'];
        $quantity = $existingOrder['quantity'];

        // Get the product details from the ProductModel
        $productModel = new ProductModel();
        $product = $productModel->find($productId);

        if ($product) {
            // Calculate the new stock after approval
            $currentStock = $product['stock'];
            $updatedStock = $currentStock - $quantity;

            // Calculate the new price based on updated stock and unit price
            $unitPrice = $product['unit_price'];
            $newPrice = $updatedStock * $unitPrice;

            // Update the product's stock and price in the database
            $productModel->set(['stock' => $updatedStock, 'price' => $newPrice])->where('ID', $productId)->update();
        }
    } elseif ($newStatus === 'cancelled' || $newStatus === 'pendingbackpricestock') {
        // Get the product ID and quantity from the order
        $productId = $existingOrder['product_id'];
        $quantity = $existingOrder['quantity'];
        $total = $existingOrder['total'];

        // Get the product details from the ProductModel
        $productModel = new ProductModel();
        $product = $productModel->find($productId);

        if ($product) {
            // Calculate the new stock after cancellation
            $currentStock = $product['stock'];
            $updatedStock = $currentStock + $quantity;

            // Calculate the new price based on updated stock and total
            $currentPrice = $product['price'];
            $newPrice = $currentPrice + $total;

            // Update the product's stock and price in the database
            $productModel->set(['stock' => $updatedStock, 'price' => $newPrice])->where('id', $productId)->update();
        }
    }

   // Update only the status if other fields are not provided
   $data = ['status' => $newStatus];
    
   // Include other fields in the update if they are provided
   if ($newQuantity !== null) {
       $data['quantity'] = $newQuantity;
   }

   if ($newTotal !== null) {
       $data['total'] = $newTotal;
   }

    
    $orderModel->set($data)->where('id', $id)->update();

    


    // Save updated data to the sales table
    $salesData = [
        'image' => $existingOrder['image'],
        'prod_name' => $existingOrder['prod_name'],
        'unit_price' => $existingOrder['unit_price'],
        'size_id' => $existingOrder['size_id'],
        'quantity' => $existingOrder['quantity'],
        'address' => $existingOrder['address'],
        'contact' => $existingOrder['contact'],
        'other_info' => $existingOrder['other_info'],
        'customerName' => $existingOrder['customerName'],
        'updated_at' => date('Y-m-d H:i:s'), // Updated timestamp
        'created_at' => $existingOrder['created_at'],
        'status' => $newStatus,
        'product_id' => $existingOrder['product_id'],
        'transaction_code' => $existingOrder['transaction_code'],
        'total' => $existingOrder['total'],
        'token' => $existingOrder['token'],
    ];

    // Save data to the SalesModel
    $salesModel = new SalesModel();
    $salesModel->insert($salesData);

      // Save updated data to the notif table
      $notifdata = [
        'image' => $existingOrder['image'],
        'prod_name' => $existingOrder['prod_name'],
        'unit_price' => $existingOrder['unit_price'],
        'size_id' => $existingOrder['size_id'],
        'quantity' => $existingOrder['quantity'],
        'address' => $existingOrder['address'],
        'contact' => $existingOrder['contact'],
        'other_info' => $existingOrder['other_info'],
        'customerName' => $existingOrder['customerName'],
        'updated_at' => date('Y-m-d H:i:s'), // Updated timestamp
        'created_at' => $existingOrder['created_at'],
        'status' => $newStatus,
        'product_id' => $existingOrder['product_id'],
        'transaction_code' => $existingOrder['transaction_code'],
        'total' => $existingOrder['total'],
        'token' => $existingOrder['token'],
    ];

    
    // Save data to the SalesModel
    $notifModel = new NotifModel();
    $notifModel->insert($notifdata);

    $notifModel->set(['status' => 'deleted'])->where('id', $id)->update();
    
    return $this->respond(['message' => 'Order status updated successfully.'], 200);
}

public function updateEventStatus()
{
    // Get the event ID and status from the request
    $eventId = $this->request->getVar('id');
    $newStatus = $this->request->getVar('status');
    $newreason = $this->request->getVar('reason');

    // Perform validation on $eventId and $newStatus

    // Assuming you have a model named EventModel to handle database operations
    $eventModel = new EventBookingModel();

    // Check if the event exists
    $event = $eventModel->find($eventId);

    if (!$event) {
        return $this->respond(['error' => 'Event not found.'], 404);
    }

    // Update the event status
    $event['status'] = $newStatus;
    $event['reason'] = $newreason;
    $updated = $eventModel->update($eventId, $event);

    if ($updated) {
        return $this->respond(['message' => 'Event status updated successfully.'], 200);
    } else {
        return $this->respond(['error' => 'Failed to update event status.'], 500);
    }
}



    public function getgeData()
    {
            $order= new OrderModel();
            $data = $order->findAll();
            return $this->respond($data, 200);
        
    }

    public function getNotif()
    {
        $notif= new NotifModel();
        $data = $notif->findAll();
        return $this->respond($data, 200);
        
    }

    public function updateNotifStatus($id)
    {
        try {
            // Retrieve status data from the POST request
            $status = $this->request->getVar('status');
    
            // Ensure valid status provided - Add more validation if needed
            if ($status !== 'deleted') {
                return $this->respond(['error' => 'Invalid status provided.'], 400);
            }
    
            // Perform the update in the NotifModel
            $notifModel = new NotifModel();
            $updated = $notifModel->update($id, ['status' => $status]);
    
            if ($updated) {
                // Assuming getOrder() retrieves updated orders after status update
                $this->getOrder(); // Refresh orders after status update
                return $this->respond(['message' => 'Notification status updated successfully.'], 200);
            } else {
                return $this->respond(['error' => 'Error updating notification status.'], 500);
            }
        } catch (\Exception $e) {
            return $this->respond(['error' => 'Error updating notification status: ' . $e->getMessage()], 500);
        }
    }
    

    
    public function deleteprod($id)
    {
        try {
            // Retrieve status data from the POST request
            $status = $this->request->getVar('status');
    
            // Ensure valid status provided - Add more validation if needed
            if ($status !== 'deleted') {
                return $this->respond(['error' => 'Invalid status provided.'], 400);
            }
    
       
            // Perform the update in the NotifModel
            $order = new OrderModel();
            $data = $order->update($id, ['status' => $status]);
    
            if ($data) {
                // Assuming getOrder() retrieves updated orders after status update
                $this->getOrder(); // Refresh orders after status update
                return $this->respond(['message' => 'Notification status updated successfully.'], 200);
            } else {
                return $this->respond(['error' => 'Error updating notification status.'], 500);
            }
        } catch (\Exception $e) {
            return $this->respond(['error' => 'Error updating notification status: ' . $e->getMessage()], 500);
        }
    }

    public function updateProfilePicture($userId)
    {
        try {
            // Retrieve the uploaded file from the request
            $profilePictureFile = $this->request->getFile('file');
    
            // Check if a file was uploaded
            if ($profilePictureFile->isValid()) {
                // Generate a unique filename for the profile picture
                $profilePictureName = 'profile_picture_' . time() . '.' . $profilePictureFile->getExtension();
    
                // Move the uploaded file to the desired directory
                $profilePictureFile->move(ROOTPATH . 'public/uploads/profile_pictures/', $profilePictureName);
    
                // Update the user's profile_picture field in the database
                $data = ['profile_picture' => base_url('uploads/profile_pictures/' . $profilePictureName)];
                $userModel = new UserModel();
                $userModel->update($userId, $data);
    
                return $this->respond(['message' => 'Profile picture updated successfully.'], 200);
            } else {
                return $this->respond(['error' => 'No valid file uploaded.'], 400);
            }
        } catch (\Exception $e) {
            return $this->respond(['error' => 'Error updating profile picture: ' . $e->getMessage()], 500);
        }
    }
    
public function updateProfile($userId)
{
    try {
        $data = [
            'showed_username' => $this->request->getVar('showed_username'),
            'contact' => $this->request->getVar('contact'),
            'address' => $this->request->getVar('address'),
            'other_info' => $this->request->getVar('other_info'),
            'legit_name' => $this->request->getVar('legit_name'),
            'gender' => $this->request->getVar('gender'),
        ];
        $userModel = new UserModel();
        $userModel->update($userId, $data);

        return $this->respond(['message' => 'Profile updated successfully.'], 200);
    } catch (\Exception $e) {
        return $this->respond(['error' => 'Error updating profile: ' . $e->getMessage()], 500);
    }
}



public function chatbot()
    {
        return view('chatbot');
    }
 
    public function getReview()
    {
      $review_model = new ReviewModel();
      $data = $review_model->findAll();
      return $this->respond($data, 200);
    }
 
    public function submitReview()
    {
        try {
            $json = $this->request->getJSON();
    
            $requestData = [
                'prod_name' => $json->prod_name,
                'product_id' => $json->product_id,
                'comment' => $json->comment,
                'rate' => $json->rate,
                'isAnonymous' => $json->isAnonymous,
                'profile_picture' => $json->profile_picture,
                'username' => $json->username,
            ];
    
            $imagePaths = [];
    
            foreach (['first_image', 'second_image', 'third_image'] as $imageField) {
                if (!empty($json->$imageField)) {
                    $decodedImageData = base64_decode($json->$imageField);
                    $imagePath = $this->saveImage($decodedImageData);
                    $requestData[$imageField] = $imagePath;
                    $imagePaths[] = $imagePath;
                }
            }
    
            $reviewModel = new ReviewModel();
            $inserted = $reviewModel->insert($requestData);
    
            if ($inserted) {
                return $this->response->setJSON(['message' => 'Review submitted successfully']);
            } else {
                foreach ($imagePaths as $imagePath) {
                    unlink($imagePath);
                }
                throw new Exception('Error submitting review');
            }
        } catch (Exception $e) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $e->getMessage()]);
        }
    }
    
    private function saveImage($imageData)
    {
        $uploadsPath = 'uploads' . DIRECTORY_SEPARATOR;
        $fileName = uniqid() . '.png';
        $filePath = $uploadsPath . $fileName;
    
        file_put_contents($filePath, $imageData);
    
        return base_url($uploadsPath . $fileName);
    }
    public function saveService()
    {
        $baseUrl = base_url(); // Add this line to get the base URL
    
        // Get form data
        $service = $this->request->getPost('service');
        $information = $this->request->getPost('information');
        $lowPricing = $this->request->getPost('low_pricing');
        $firstReq = $this->request->getPost('first_req');
        $secondReq = $this->request->getPost('second_req');
        $thirdReq = $this->request->getPost('third_req');
        $fourthReq = $this->request->getPost('fourth_req');
        $fifthReq = $this->request->getPost('fifth_req');
        $sixthReq = $this->request->getPost('sixth_req');
        $firstPrice = $this->request->getPost('first_price');
        $secondPrice = $this->request->getPost('second_price');
        $thirdPrice = $this->request->getPost('third_price');
        $fourthPrice = $this->request->getPost('fourth_price');
        $fifthPrice = $this->request->getPost('fifth_price');
        $sixthPrice = $this->request->getPost('sixth_price');
    
        // Handle image upload
        $image = $this->request->getFile('image');
        $imageExtension = $image->getExtension();
        $generatedFolderName = uniqid();
        $generatedImageName = $generatedFolderName . '.' . $imageExtension;
    
        // Move the image to the folder
        $imagePath = FCPATH . 'uploads/' . $generatedFolderName . '/';
        $image->move($imagePath, $generatedImageName);
    
        // Save other data to the database
        $data = [
            'service' => $service,
            'information' => $information,
            'low_pricing' => $lowPricing,
            'image' => $baseUrl . 'uploads/' . $generatedFolderName . '/' . $generatedImageName, // Set the full image URL with base URL
            'first_req' => $firstReq,
            'second_req' => $secondReq,
            'third_req' => $thirdReq,
            'fourth_req' => $fourthReq,
            'fifth_req' => $fifthReq,
            'sixth_req' => $sixthReq,
            'first_price' => $firstPrice,
            'second_price' => $secondPrice,
            'third_price' => $thirdPrice,
            'fourth_price' => $fourthPrice,
            'fifth_price' => $fifthPrice,
            'sixth_price' => $sixthPrice,
        ];
    
        $serviceModel = new ServicesModel();
        $datas = $serviceModel->save($data);
    
        return $this->respond($datas, 200);
    }
    
    
    
    
}


//final copy