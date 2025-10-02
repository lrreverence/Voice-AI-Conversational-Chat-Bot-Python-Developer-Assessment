<?php

namespace Modules\ContactManager\Http\Controllers;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Modules\ContactManager\Entities\Contact;

class ContactCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    public function setup()
    {
        CRUD::setModel(Contact::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/contact');
        CRUD::setEntityNameStrings('contact', 'contacts');
    }

    protected function setupListOperation()
    {
        CRUD::column('name')->label('Name');
        CRUD::column('email')->label('Email');
        CRUD::column('phone')->label('Phone');
        CRUD::column('image')->label('Image')->type('image');
        CRUD::column('created_at')->label('Created At');
    }

    protected function setupCreateOperation()
    {
        CRUD::setValidation([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        CRUD::field('name')->label('Name')->type('text');
        CRUD::field('email')->label('Email')->type('email');
        CRUD::field('phone')->label('Phone')->type('text');
        CRUD::field('address')->label('Address')->type('textarea');
        CRUD::field('notes')->label('Notes')->type('textarea');
        CRUD::field('image')->label('Image')->type('upload')->disk('public')->path('contacts');
    }

    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
        
        // Update validation to allow current email
        CRUD::setValidation([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:contacts,email,' . request()->route('id'),
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);
    }
}
