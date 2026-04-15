<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Notification;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    use ApiResponseTrait;
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email||regex:/^[\w\.-]+@[\w\.-]+\.[a-zA-Z]{2,}$/|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);

        $contact = Contact::create($request->all());
        $admins = User::where('role', 'admin')->get();

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'message' => 'New message from ' . $request->name . ' (' . $request->email . ')'
            ]);
        }
        return $this->created('Your message has been sent successfully', $contact);
    }
    public function index()
    {
        $contacts = Contact::all();
        return $this->success('Contact messages retrieved successfully', $contacts);
    }

    public function show(Contact $contact)
    {
        return $this->success('Contact message retrieved successfully', $contact);
    }
    public function destroy(Contact $contact)
    {
        $contact->delete();
        return $this->deleted('Contact message deleted successfully');
    }
}
