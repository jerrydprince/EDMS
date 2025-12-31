<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       //In your archives migration file
        // Schema::table('archives', function (Blueprint $table) {
        //     $table->index('department_id');
        //     $table->index('division_id');
        //     $table->index('unit_id');
        //     $table->index('created_at');
        //     $table->index('updated_at');
        // });

        // Schema::table('file_paths', function (Blueprint $table) {
        //     $table->index('file_name'); // For LIKE searches - partial index
        //     $table->index('updated_by_name'); // For LIKE searches
        //    // $table->index('archive_id'); // Foreign key if not already indexed
            
        //     // For better LIKE performance, consider fulltext:
        //    // $table->fullText(['file_name', 'updated_by_name']);
        // });

        Schema::table('archive_docs', function (Blueprint $table) {
            //$table->index('archive_id'); // Foreign key
           // $table->index('archive_keys'); // For filtering by 'title'
           // $table->index(['archive_keys', 'archive_id']); // Composite index
            
            // For date filtering (if archive_values stores dates)
            //$table->index('archive_values'); // Only if it's a date column
            
            // For text search
           // $table->fullText('archive_values'); // For LIKE searches
        });

        Schema::table('folder', function (Blueprint $table) {
           // $table->index('department_id');
            // $table->index('division_id');
            // $table->index('unit_id');
            // $table->index('name');
            // $table->index('number');
            
            // Fulltext for better LIKE performance
           // $table->fullText(['name', 'number']);
        });

        // Schema::table('departments', function (Blueprint $table) {
        //     $table->index('title');
        //     $table->index(['id', 'title']); // Composite for your specific query
        // });

        // Schema::table('divisions', function (Blueprint $table) {
        //     $table->index('title');
        //     $table->index('department_id'); // If exists
        //     $table->index(['id', 'title']); // Composite for your specific query
        // });

        // Schema::table('units', function (Blueprint $table) {
        //     $table->index('title');
        //     $table->index('division_id'); // If exists
        //     $table->index('department_id'); // If exists
        //     $table->index(['id', 'title']); // Composite for your specific query
        //     $table->index('document_type_id'); // For the documentType relationship
        // });

        Schema::table('document_types', function (Blueprint $table) {
            $table->index('name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('department_id');
            $table->index('division_id');
            $table->index('unit_id');
            // role_id if you're using a role_id column
        });

        Schema::table('recent_searches', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('query_string');
            $table->index(['user_id', 'created_at']); // For latest() queries
            $table->unique('query_string'); // Since you're using updateOrCreate
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('search_tables', function (Blueprint $table) {
            //
        });
    }
};
