<?php

namespace Modules\ContactManager\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ContactManager\Entities\Contact;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts
     */
    public function index(Request $request): JsonResponse
    {
        $query = Contact::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 10);
        $contacts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $contacts
        ]);
    }

    /**
     * Store a newly created contact
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $contact = Contact::create($request->all());

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('contacts', 'public');
            $contact->update(['image' => basename($imagePath)]);
        }

        return response()->json([
            'success' => true,
            'data' => $contact,
            'message' => 'Contact created successfully'
        ], 201);
    }

    /**
     * Display the specified contact
     */
    public function show(Contact $contact): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $contact
        ]);
    }

    /**
     * Update the specified contact
     */
    public function update(Request $request, Contact $contact): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email,' . $contact->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $contact->update($request->all());

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('contacts', 'public');
            $contact->update(['image' => basename($imagePath)]);
        }

        return response()->json([
            'success' => true,
            'data' => $contact,
            'message' => 'Contact updated successfully'
        ]);
    }

    /**
     * Remove the specified contact
     */
    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact deleted successfully'
        ]);
    }
}
