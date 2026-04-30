<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = Registration::orderBy('id', 'DESC')->get();

        return view('backend.career.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->except('_token', 'g-recaptcha-response');
       
        Registration::create($data);

        return redirect()->back()->with('success', 'Data saved successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Registration  $registration
     * @return \Illuminate\Http\Response
     */
    public function show(Registration $registration)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Registration  $registration
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $id = decrypt($id);

        $data['data'] = Registration::whereId($id)->first();

        $data['states'] = [
            "Alabama","Alaska","Arizona","Arkansas","California","Colorado","Connecticut",
            "Delaware","District Of Columbia","Florida","Georgia","Hawaii","Idaho","Illinois",
            "Indiana","Iowa","Kansas","Kentucky","Louisiana","Maine","Maryland","Massachusetts",
            "Michigan","Minnesota","Mississippi","Missouri","Montana","Nebraska","Nevada",
            "New Hampshire","New Jersey","New Mexico","New York","North Carolina","North Dakota",
            "Ohio","Oklahoma","Oregon","Pennsylvania","Puerto Rico","Rhode Island","South Carolina",
            "South Dakota","Tennessee","Texas","Utah","Vermont","Virgin Islands","Virginia",
            "Washington","West Virginia","Wisconsin","Wyoming"
        ];

        return view('backend.career.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Registration  $registration
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
     
        $data = Registration::findOrFail($id);
      
        if($request->type === "Profile"){
         
            $data->Position_Applying_For = $request->Position_Applying_For;
            $data->First_Name = $request->First_Name;
            $data->Last_Name = $request->Last_Name;
            $data->Email_Address = $request->Email_Address;
            $data->Mobile_Phone = $request->Mobile_Phone;
            $data->Address = $request->Address;
            $data->City = $request->City;
            $data->Zip = $request->Zip;
            $data->State = $request->State;
            $data->Desired_Hourly_Pay = $request->Desired_Hourly_Pay;
            $data->Skills = $request->Skills;
            $data->Preferred_Date_to_Start_Working = $request->Preferred_Date_to_Start_Working;

        } elseif ($request->type === "Emergency"){

            $data->Emergency_Contact_Name = $request->Emergency_Contact_Name;
            $data->Emergency_Contact_Phone = $request->Emergency_Contact_Phone;
            $data->Emergency_Contact_Relationship = $request->Emergency_Contact_Relationship;

        } elseif ($request->type === "License"){

            $data->Professional_License = $request->Professional_License;
            $data->Previous_License_State = $request->Previous_License_State;
            $data->Professional_License_Expiration = $request->Professional_License_Expiration;

            $data->ACLS = $request->ACLS;
            $data->ACLS_License_Expiration = $request->ACLS_License_Expiration;

            $data->Other_License = $request->Other_License;

            $data->Specialty_Nurse_1 = $request->Specialty_Nurse_1 ?? null;
            $data->Specialty_Nurse_2 = $request->Specialty_Nurse_2 ?? null;
            $data->Specialty_Nurse_3 = $request->Specialty_Nurse_3 ?? null;
            $data->Specialty_Nurse_4 = $request->Specialty_Nurse_4 ?? null;
            $data->Specialty_Nurse_5 = $request->Specialty_Nurse_5 ?? null;
            $data->Specialty_Nurse_6 = $request->Specialty_Nurse_6 ?? null;
            $data->Specialty_Nurse_7 = $request->Specialty_Nurse_7 ?? null;
            $data->Specialty_Nurse_8 = $request->Specialty_Nurse_8 ?? null;
            $data->Specialty_Nurse_9 = $request->Specialty_Nurse_9 ?? null;
            
        } elseif ($request->type === "Reference"){

            $data->First_Reference_Name = $request->First_Reference_Name;
            $data->First_Reference_Phone_Number = $request->First_Reference_Phone_Number;
            $data->First_Reference_Relationship = $request->First_Reference_Relationship;

            $data->Second_Reference_Name = $request->Second_Reference_Name;
            $data->Second_Reference_Phone_Number = $request->Second_Reference_Phone_Number;
            $data->Second_Reference_Relationship = $request->Second_Reference_Relationship;

            // First Previous Employment
            $data->Previous_Employment_Company_Name = $request->Previous_Employment_Company_Name;
            $data->Previous_Employment_Supervisor_Name = $request->Previous_Employment_Supervisor_Name;
            $data->Previous_Employment_Phone_Number = $request->Previous_Employment_Phone_Number;
            $data->Month_first = $request->Month_first;
            $data->Year_first = $request->Year_first;
            $data->Month_second = $request->Month_second;
            $data->Year_second = $request->Year_second;

            // Second Previous Employment
            $data->Previous_Employment_Company_Name_ = $request->Previous_Employment_Company_Name_;
            $data->Previous_Employment_Supervisor_Name_ = $request->Previous_Employment_Supervisor_Name_;
            $data->Previous_Employment_Phone_Number_ = $request->Previous_Employment_Phone_Number_;
            $data->_Month = $request->_Month;
            $data->_Year = $request->_Year;
            $data->_Month_ = $request->_Month_;
            $data->_Year_ = $request->_Year_;
            
        } elseif ($request->type === "Education"){

            $data->School_Name = $request->School_Name;
            $data->School_Location = $request->School_Location;
            $data->School_Degree_or_Level = $request->School_Degree_or_Level;
            $data->School_Graduation_or_Certification_Year = $request->School_Graduation_or_Certification_Year;

            $data->School_Name_ = $request->School_Name_;
            $data->School_Location_ = $request->School_Location_;
            $data->School_Degree_or_Level_ = $request->School_Degree_or_Level_;
            $data->School_Graduation_or_Certification_Year_ = $request->School_Graduation_or_Certification_Year_;
            
        }
 
        $data->save();

        return redirect()->route('admin.careers.list')->with('success', 'Registration updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Registration  $registration
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $id = decrypt($id);

        Registration::delete($id);

        return back()->with('error', 'Registration delete successfully');
    }
}
