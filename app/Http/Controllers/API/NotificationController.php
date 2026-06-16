<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MessageTarget;
use Kreait\Firebase\Exception\FirebaseException;

class NotificationController extends Controller
{
     public function send(Request $request)
    {
        try {

            $factory = (new Factory)
                ->withServiceAccount(storage_path('app/firebase_creditionals.json'));

            // Get the messaging service
            $messaging = $factory->createMessaging();
            $request->validate([
                'token' => 'required|string',
                'title' => 'required|string',
                'body'  => 'required|string'

            ]);
            $token = $request->input('token');
            $title = $request->input('title', 'Default Title');
            $body = $request->input('body', 'Default Body');

            // Create the message
            $data = [
                'title' => $title,
                'body' => $body,
            ];
            $message = CloudMessage::new()
                ->withTarget(MessageTarget::TOKEN, $token)
                ->withNotification($data);

            // Send the message
            $response = $messaging->send($message);

            // Return the response from FCM
            return ['success' => true, 'response' => $response];
        } catch (FirebaseException $e) {
            // Handle Firebase-specific errors
            return ['error' => 'Firebase error: ' . $e->getMessage()];
        } catch (\Exception $e) {
            // Handle any other errors
            return ['error' => $e->getMessage()];
        }
    }
}
