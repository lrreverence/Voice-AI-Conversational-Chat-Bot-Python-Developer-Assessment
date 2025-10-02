<?php

namespace Modules\ContactManager\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\ContactManager\Entities\Contact;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1-555-0123',
                'address' => '123 Main St, New York, NY 10001',
                'notes' => 'Important client, prefers email communication'
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+1-555-0456',
                'address' => '456 Oak Ave, Los Angeles, CA 90210',
                'notes' => 'Marketing director, very responsive'
            ],
            [
                'name' => 'Bob Johnson',
                'email' => 'bob.johnson@example.com',
                'phone' => '+1-555-0789',
                'address' => '789 Pine St, Chicago, IL 60601',
                'notes' => 'Technical lead, prefers phone calls'
            ],
            [
                'name' => 'Alice Brown',
                'email' => 'alice.brown@example.com',
                'phone' => '+1-555-0321',
                'address' => '321 Elm St, Houston, TX 77001',
                'notes' => 'Project manager, very organized'
            ],
            [
                'name' => 'Charlie Wilson',
                'email' => 'charlie.wilson@example.com',
                'phone' => '+1-555-0654',
                'address' => '654 Maple Dr, Phoenix, AZ 85001',
                'notes' => 'Sales representative, great networker'
            ]
        ];

        foreach ($contacts as $contact) {
            Contact::create($contact);
        }
    }
}
