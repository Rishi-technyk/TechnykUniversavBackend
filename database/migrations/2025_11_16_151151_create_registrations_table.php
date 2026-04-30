<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();

            $table->string('Position_Applying_For')->nullable();
            $table->string('First_Name')->nullable();
            $table->string('Last_Name')->nullable();
            $table->string('Email_Address')->nullable();
            $table->string('Mobile_Phone')->nullable();
            $table->text('Address')->nullable();
            $table->string('City')->nullable();
            $table->string('Zip')->nullable();
            $table->string('State')->nullable();
            $table->string('Desired_Hourly_Pay')->nullable();
            $table->text('Skills')->nullable();
            $table->string('Preferred_Date_to_Start_Working')->nullable();
            $table->string('Preferred_Shifts')->nullable();

            // Emergency Contact
            $table->string('Emergency_Contact_Name')->nullable();
            $table->string('Emergency_Contact_Phone')->nullable();
            $table->string('Emergency_Contact_Relationship')->nullable();

            // License Information
            $table->string('Previous_License_State')->nullable();
            $table->string('Professional_License_Expiration')->nullable();
            $table->string('ACLS_License_Expiration')->nullable();
            $table->text('Specify_or_Notes')->nullable();
            $table->string('Specialty_Nurse_Others')->nullable();

            // References
            $table->string('First_Reference_Name')->nullable();
            $table->string('First_Reference_Relationship')->nullable();
            $table->string('First_Reference_Phone_Number')->nullable();
            $table->string('Second_Reference_Name')->nullable();
            $table->string('Second_Reference_Relationship')->nullable();
            $table->string('Second_Reference_Phone_Number')->nullable();

            // First Previous Employment
            $table->string('Previous_Employment_Company_Name')->nullable();
            $table->string('Previous_Employment_Supervisor_Name')->nullable();
            $table->string('Previous_Employment_Phone_Number')->nullable();
            $table->string('Month_first')->nullable();
            $table->string('Year_first')->nullable();
            $table->string('Month_second')->nullable();
            $table->string('Year_second')->nullable();

            // Second Previous Employment
            $table->string('Previous_Employment_Company_Name_')->nullable();
            $table->string('Previous_Employment_Supervisor_Name_')->nullable();
            $table->string('Previous_Employment_Phone_Number_')->nullable();
            $table->string('_Month')->nullable();
            $table->string('_Year')->nullable();
            $table->string('_Month_')->nullable();
            $table->string('_Year_')->nullable();

            // Education - 1
            $table->string('School_Name')->nullable();
            $table->string('School_Location')->nullable();
            $table->string('School_Degree_or_Level')->nullable();
            $table->string('School_Graduation_or_Certification_Year')->nullable();

            // Education - 2
            $table->string('School_Name_')->nullable();
            $table->string('School_Location_')->nullable();
            $table->string('School_Degree_or_Level_')->nullable();
            $table->string('School_Graduation_or_Certification_Year_')->nullable();

            // Education - 3
            $table->string('School_Name__')->nullable();
            $table->string('School_Location__')->nullable();
            $table->string('School_Degree_or_Level__')->nullable();
            $table->string('School_Graduation_or_Certification_Year__')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('registrations');
    }
};
