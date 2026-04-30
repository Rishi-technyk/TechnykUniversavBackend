<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\Models\SOP;
use App\Models\User;
use App\Models\Member;
use App\Models\Session;
use App\Models\Feedback;
use App\Models\BlockRoom;
use App\Models\VenueBlock;
use App\Models\VenueMaster;
use App\Models\VenueCharge;
use App\Models\RoomBooking;
use Illuminate\Http\Request;
use App\Models\CategoryType;
use App\Models\AdminSetting;
use App\Models\OccupantMaster;
use App\Models\FunctionMaster;
use App\Models\CategoryMaster;
use App\Models\RoomBookingItem;
use App\Models\RoomChargesMaster;
use App\Models\RoomCategoryMaster;
use App\Models\CancellationPolicy;
use Illuminate\Support\Facades\Hash;
use App\Models\BanquetBookingCharges;
use App\Models\RoomCancellationPolicy;
use App\Models\CircularsCategory;
use App\Models\Circular;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{

    public function venue_master(Request $request)
    {
        $data['datas'] = VenueMaster::orderBy('id', 'DESC')->get();

        return view('superadmin.venue_master.index',$data);
    }

    public function venue_master_add(Request $request)
    {
        return view('superadmin.venue_master.create');
    }

    public function venue_master_store(Request $request)
    {
        $params['name']         = $request->name;
        $params['capacity']     = $request->capacity;
        $params['GSTper']       = $request->GSTper;
        $params['security_deposit'] = $request->security_deposit;

        $res = VenueMaster::create($params);

        if($res){
            return redirect()->route('venue.master')->with('success', 'Data Store Successfully.'); 
        } else {
           return redirect()->route('venue.master')->with('error', 'Try Again.');  
        }
    }

    public function venue_master_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = VenueMaster::find($id);
        
        return view('superadmin.venue_master.edit',$data);

    }

    public function venue_master_update(Request $request)
    {
        $params['name']         = $request->name;
        $params['capacity']     = $request->capacity;
        $params['GSTper']       = $request->GSTper;
        $params['security_deposit'] = $request->security_deposit;

        $res = VenueMaster::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('venue.master')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('venue.master')->with('error', 'Try Again.');  
        }
    }

    public function venue_master_delete($id)
    {
        $id = decrypt($id);

        VenueMaster::whereId($id)->delete();
        
        return redirect()->route('venue.master')->with('error', 'Delete Successfully.'); 

    }

    public function venue_master_status($id)
    {
        $id = decrypt($id);

        $data = VenueMaster::find($id);

        if($data->status=='Active'){
            $params['status'] = 'Inactive';
        } else {
            $params['status'] = 'Active';
        }

        VenueMaster::whereId($id)->update($params);
        
        return redirect()->route('venue.master')->with('success', 'Status Change Successfully.'); 

    }

    public function session_master(Request $request)
    {
        $data['datas'] = Session::orderBy('id', 'DESC')->get();

        return view('superadmin.session_master.index',$data);
    }

    public function session_master_add(Request $request)
    {
        return view('superadmin.session_master.create');
    }

    public function session_master_store(Request $request)
    {
        $params['name']         = $request->name;

        $res = Session::create($params);

        if($res){
            return redirect()->route('session.master')->with('success', 'Data Store Successfully.'); 
        } else {
           return redirect()->route('session.master')->with('error', 'Try Again.');  
        }
    }

    public function session_master_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = Session::find($id);
        
        return view('superadmin.session_master.edit',$data);

    }

    public function session_master_update(Request $request)
    {
        $params['name']         = $request->name;

        $res = Session::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('session.master')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('session.master')->with('error', 'Try Again.');  
        }
    }

    public function session_master_delete($id)
    {
        $id = decrypt($id);

        Session::whereId($id)->delete();
        
        return redirect()->route('session.master')->with('error', 'Delete Successfully.'); 

    }

    public function session_master_status($id)
    {
        $id = decrypt($id);

        $data = Session::find($id);

        if($data->status=='Active'){
            $params['status'] = 'Inactive';
        } else {
            $params['status'] = 'Active';
        }

        Session::whereId($id)->update($params);
        
        return redirect()->route('session.master')->with('success', 'Status Change Successfully.'); 

    }


    public function occupant_master(Request $request)
    {
        $data['datas'] = OccupantMaster::orderBy('id', 'DESC')->get();

        return view('superadmin.occupant_master.index',$data);
    }

    public function occupant_master_add(Request $request)
    {
        return view('superadmin.occupant_master.create');
    }

    public function occupant_master_store(Request $request)
    {
        $params['name']         = $request->name;
        $params['additional_info']         = $request->additional_info;

        $res = OccupantMaster::create($params);

        if($res){
            return redirect()->route('occupant.master')->with('success', 'Data Store Successfully.'); 
        } else {
           return redirect()->route('occupant.master')->with('error', 'Try Again.');  
        }
    }

    public function occupant_master_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = OccupantMaster::find($id);
        
        return view('superadmin.occupant_master.edit',$data);

    }

    public function occupant_master_update(Request $request)
    {
        $params['name']         = $request->name;
        $params['additional_info']         = $request->additional_info;

        $res = OccupantMaster::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('occupant.master')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('occupant.master')->with('error', 'Try Again.');  
        }
    }

    public function occupant_master_delete($id)
    {
        $id = decrypt($id);

        OccupantMaster::whereId($id)->delete();
        
        return redirect()->route('occupant.master')->with('error', 'Delete Successfully.'); 

    }

    public function occupant_master_status($id)
    {
        $id = decrypt($id);

        $data = OccupantMaster::find($id);

        if($data->status=='Active'){
            $params['status'] = 'Inactive';
        } else {
            $params['status'] = 'Active';
        }

        OccupantMaster::whereId($id)->update($params);
        
        return redirect()->route('occupant.master')->with('success', 'Status Change Successfully.'); 

    }

    public function venue_charges(Request $request)
    {
        $data['datas'] = VenueCharge::orderBy('id', 'DESC')->get();

        return view('superadmin.venue_charges.index',$data);
    }

    public function venue_charges_add(Request $request)
    {
        $data['occupant']   = OccupantMaster::where('status', 'Active')->get();
        $data['venue']      = VenueMaster::where('status', 'Active')->get();
        $data['session']    = Session::where('status', 'Active')->get();

        return view('superadmin.venue_charges.create', $data);
    }

    public function venue_charges_store(Request $request)
    {
        if(VenueCharge::where('venue_id', $request->venue_id)->where('session_id', $request->session_id)->where('occupant_id', $request->occupant_id)->exists()){
            return redirect()->back()->with('error', 'This Venue alredy exists.');
        }

        $params['venue_id']     = $request->venue_id;
        $params['session_id']   = $request->session_id;
        $params['occupant_id']  = $request->occupant_id;
        $params['rate']         = $request->rate;

        $res = VenueCharge::create($params);

        if($res){
            return redirect()->route('venue.charge')->with('success', 'Data Store Successfully.'); 
        } else {
           return redirect()->route('venue.charge')->with('error', 'Try Again.');  
        }
    }

    public function venue_charges_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = VenueCharge::find($id);

        $data['occupant']   = OccupantMaster::where('status', 'Active')->get();
        $data['venue']      = VenueMaster::where('status', 'Active')->get();
        $data['session']    = Session::where('status', 'Active')->get();
        
        return view('superadmin.venue_charges.edit',$data);

    }

    public function venue_charges_update(Request $request)
    {
        if(VenueCharge::where('id', '!=', $request->id)->where('venue_id', $request->venue_id)->where('session_id', $request->session_id)->where('occupant_id', $request->occupant_id)->exists()){
            return redirect()->back()->with('error', 'This Venue alredy exists.');
        }

        $params['venue_id']     = $request->venue_id;
        $params['session_id']   = $request->session_id;
        $params['occupant_id']  = $request->occupant_id;
        $params['rate']         = $request->rate;

        $res = VenueCharge::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('venue.charge')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('venue.charge')->with('error', 'Try Again.');  
        }
    }

    public function venue_charges_delete($id)
    {
        $id = decrypt($id);

        VenueCharge::whereId($id)->delete();
        
        return redirect()->route('venue.charge')->with('error', 'Delete Successfully.'); 

    }


    public function venue_block(Request $request)
    {
        $data['datas'] = VenueBlock::orderBy('id', 'DESC')->get();

        return view('superadmin.venue_block.index',$data);
    }

    public function venue_block_add(Request $request)
    {
        $data['venue']      = VenueMaster::where('status', 'Active')->get();
        $data['session']    = Session::where('status', 'Active')->get();

        return view('superadmin.venue_block.create', $data);
    }

    public function venue_block_store(Request $request)
    {
        if(VenueBlock::where('venue_id', $request->venue_id)->where('session_id', $request->session_id)->exists()){
            return redirect()->back()->with('error', 'This Venue alredy exists.');
        }

        $params['venue_id']     = $request->venue_id;
        $params['session_id']   = $request->session_id;
        $params['from_date']    = $request->from_date;
        $params['to_date']      = $request->to_date;
        $params['remark']       = $request->remark;

        $res = VenueBlock::create($params);

        if($res){
            return redirect()->route('venue.block')->with('success', 'Data Store Successfully.'); 
        } else {
           return redirect()->route('venue.block')->with('error', 'Try Again.');  
        }
    }

    public function venue_block_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = VenueBlock::find($id);

        $data['venue']      = VenueMaster::where('status', 'Active')->get();
        $data['session']    = Session::where('status', 'Active')->get();
        
        return view('superadmin.venue_block.edit',$data);

    }

    public function venue_block_update(Request $request)
    {
        if(VenueBlock::where('id', '!=', $request->id)->where('venue_id', $request->venue_id)->where('session_id', $request->session_id)->exists()){
            return redirect()->back()->with('error', 'This Venue alredy exists.');
        }

        $params['venue_id']     = $request->venue_id;
        $params['session_id']   = $request->session_id;
        $params['from_date']    = $request->from_date;
        $params['to_date']      = $request->to_date;
        $params['remark']       = $request->remark;

        $res = VenueBlock::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('venue.block')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('venue.block')->with('error', 'Try Again.');  
        }
    }

    public function venue_block_delete($id)
    {
        $id = decrypt($id);

        VenueBlock::whereId($id)->delete();
        
        return redirect()->route('venue.block')->with('error', 'Delete Successfully.'); 

    }

    public function cancellation_policy(Request $request)
    {
        $data['datas'] = CancellationPolicy::orderBy('id', 'DESC')->get();

        return view('superadmin.cancellation_policy.index',$data);
    }

    public function cancellation_policy_add(Request $request)
    {
        $data['venue'] = VenueMaster::where('status', 'Active')->get();

        return view('superadmin.cancellation_policy.create', $data);
    }

    public function cancellation_policy_store(Request $request)
    {
        $params['from_days']   = $request->from_days;
        $params['to_days']     = $request->to_days;
        $params['deduction']   = $request->deduction;
        $params['GST']         = $request->GST;
        $params['venue_id']    = $request->venue_id;

        $res = CancellationPolicy::create($params);

        if($res){
            return redirect()->route('cancellation.policy')->with('success', 'Data Store Successfully.'); 
        } else {
           return redirect()->route('cancellation.policy')->with('error', 'Try Again.');  
        }
    }

    public function cancellation_policy_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = CancellationPolicy::find($id);

        $data['venue'] = VenueMaster::where('status', 'Active')->get();
        
        return view('superadmin.cancellation_policy.edit',$data);

    }

    public function cancellation_policy_update(Request $request)
    {
        $params['from_days']   = $request->from_days;
        $params['to_days']     = $request->to_days;
        $params['deduction']   = $request->deduction;
        $params['GST']         = $request->GST;
        $params['venue_id']    = $request->venue_id;

        $res = CancellationPolicy::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('cancellation.policy')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('cancellation.policy')->with('error', 'Try Again.');  
        }
    }

    public function cancellation_policy_delete($id)
    {
        $id = decrypt($id);

        CancellationPolicy::whereId($id)->delete();
        
        return redirect()->route('cancellation.policy')->with('error', 'Delete Successfully.'); 

    }

    public function function_master(Request $request)
    {
        $data['datas'] = FunctionMaster::orderBy('id', 'DESC')->get();

        return view('superadmin.function_master.index',$data);
    }

    public function function_master_add(Request $request)
    {
        return view('superadmin.function_master.create');
    }

    public function function_master_store(Request $request)
    {
        $params['name']         = $request->name;
        $params['description']  = $request->description;

        $res = FunctionMaster::create($params);

        if($res){
            return redirect()->route('function.master')->with('success', 'Data Store Successfully.'); 
        } else {
           return redirect()->route('function.master')->with('error', 'Try Again.');  
        }
    }

    public function function_master_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = FunctionMaster::find($id);
        
        return view('superadmin.function_master.edit',$data);

    }

    public function function_master_update(Request $request)
    {
        $params['name']         = $request->name;
        $params['description']  = $request->description;

        $res = FunctionMaster::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('function.master')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('function.master')->with('error', 'Try Again.');  
        }
    }

    public function function_master_delete($id)
    {
        $id = decrypt($id);

        FunctionMaster::whereId($id)->delete();
        
        return redirect()->route('function.master')->with('error', 'Delete Successfully.'); 

    }

    public function function_master_status($id)
    {
        $id = decrypt($id);

        $data = FunctionMaster::find($id);

        if($data->status=='Active'){
            $params['status'] = 'Inactive';
        } else {
            $params['status'] = 'Active';
        }

        FunctionMaster::whereId($id)->update($params);
        
        return redirect()->route('function.master')->with('success', 'Status Change Successfully.'); 

    }

    public function admin_setting(Request $request)
    {
        $data['setting'] = AdminSetting::first();

        return view('superadmin.setting.index', $data);
    }

    public function admin_setting_store(Request $request)
    {
        $setting = AdminSetting::first();

        $params['heading']      = $request->heading;
        $params['sub_heading']  = $request->sub_heading;
        $params['phone']        = $request->phone;
        $params['email']        = $request->email;
        $params['min_days']     = $request->min_days;
        $params['max_days']     = $request->max_days;

        if(Auth::check() && Auth::user()->role=='Banquet Manager'){
            $params['banquest_booking_form']            = $request->banquest_booking_form=='1' ? 'Active' : 'Inactive';
            $params['banquest_booking_transaction']     = $request->banquest_booking_transaction=='1' ? 'Active' : 'Inactive';
            $params['banquest_booking_availability']    = $request->banquest_booking_availability=='1' ? 'Active' : 'Inactive';
        }
        
        if(Auth::check() && Auth::user()->role=='Room Manager'){
            $params['room_booking_module']        = $request->room_booking_module=='1' ? 'Active' : 'Inactive';
            $params['room_booking_transaction']   = $request->room_booking_transaction=='1' ? 'Active' : 'Inactive';
            $params['room_booking_availability']  = $request->room_booking_availability=='1' ? 'Active' : 'Inactive';
        }
    
        if($setting){
            AdminSetting::whereId($setting->id)->update($params);
        } else {
            AdminSetting::create($params);
        }

        return redirect()->back()->with('success', 'Update Successfully.'); 
        
    }

    public function bookings(Request $request)
    {
        $data['session'] = Session::where('status', 'Active')->get();
        $data['occupant_type'] = OccupantMaster::where('status', 'Active')->get();

        $q = BanquetBookingCharges::query();

            
              if ($request->fundate)
              {
                 $q->whereDate('funDate', $request->fundate);
              }

              if ($request->session)
              {
                 $q->where('session_id', $request->session);
              }

              if ($request->card_id)
              {
                $cID = $request->card_id;

                $q->whereHas('banquet', function ($query) use ($cID) {
                    $query->where('cardID', $cID);
                });
              }

              if ($request->member_id)
              {
                $MID = $request->member_id;

                $q->whereHas('banquet', function ($query) use ($MID) {
                    $query->where('memberID', $MID);
                });
              }

              if ($request->occupant_type)
              {
                $OTy = $request->occupant_type;

                $q->whereHas('banquet', function ($query) use ($OTy) {
                    $query->where('occupant_type', $OTy);
                });
              }

              if ($request->status)
              {
                 $q->where('status', $request->status);
              }

        $data['datas'] = $q->orderBy('id', 'DESC')->get();

        $data['request'] = $request;
        
        return view('superadmin.booking.index', $data);
    }

    public function cancellation_bookings(Request $request)
    {
        $data['session'] = Session::where('status', 'Active')->get();
        $data['occupant_type'] = OccupantMaster::where('status', 'Active')->get();

        $q = BanquetBookingCharges::query();

            
              if ($request->fundate)
              {
                 $q->whereDate('funDate', $request->fundate);
              }

              if ($request->session)
              {
                 $q->where('session_id', $request->session);
              }

              if ($request->card_id)
              {
                $cID = $request->card_id;

                $q->whereHas('banquet', function ($query) use ($cID) {
                    $query->where('cardID', $cID);
                });
              }

              if ($request->member_id)
              {
                $MID = $request->member_id;

                $q->whereHas('banquet', function ($query) use ($MID) {
                    $query->where('memberID', $MID);
                });
              }

              if ($request->occupant_type)
              {
                $OTy = $request->occupant_type;

                $q->whereHas('banquet', function ($query) use ($OTy) {
                    $query->where('occupant_type', $OTy);
                });
              }

              $q->where('status', 'Cancelled');

        $data['datas'] = $q->orderBy('id', 'DESC')->get();

        $data['request'] = $request;
        
        return view('superadmin.booking.cancellation_bookings', $data);
    }

    public function category_master(Request $request)
    {
        $data['datas'] = CategoryMaster::orderBy('Catg_Code', 'DESC')->get();

        return view('superadmin.category_master.index',$data);
    }

    public function category_master_add(Request $request)
    {
        return view('superadmin.category_master.create');
    }

    public function category_master_store(Request $request)
    {
        $check = CategoryMaster::where('Catg_Name', $request->name)->exists();

        if($check){
            return back()->with('error', 'This Category already exists.');
        }

        $params['Catg_Name']    = $request->name;

        $res = CategoryMaster::create($params);

        if($res){
            return redirect()->route('category.master')->with('success', 'Data Store Successfully.'); 
        } else {
           return redirect()->route('category.master')->with('error', 'Try Again.');  
        }
    }

    public function category_master_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = CategoryMaster::where('Catg_Code', $id)->first();
        
        return view('superadmin.category_master.edit',$data);

    }

    public function category_master_update(Request $request)
    {
        $check = CategoryMaster::where('Catg_Name', $request->name)->where('Catg_Code', '!=', $request->id)->exists();

        if($check){
            return back()->with('error', 'This Category already exists.');
        }
        
        $params['Catg_Name'] = $request->name;

        $res = CategoryMaster::where('Catg_Code', $request->id)->update($params);

        if($res){
            return redirect()->route('category.master')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('category.master')->with('error', 'Try Again.');  
        }
    }

    public function category_master_delete($id)
    {
        $id = decrypt($id);

        CategoryMaster::where('Catg_Code', $id)->delete();
        
        return redirect()->route('category.master')->with('error', 'Delete Successfully.'); 

    }

    public function category_master_status($id)
    {
        $id = decrypt($id);

        $data = CategoryMaster::where('Catg_Code', $id)->first();

        if($data->status=='Active'){
            $params['status'] = 'Inactive';
        } else {
            $params['status'] = 'Active';
        }

        CategoryMaster::where('Catg_Code', $id)->update($params);
        
        return redirect()->route('category.master')->with('success', 'Status Change Successfully.'); 

    }


    public function category_type(Request $request)
    {
        $data['datas'] = CategoryType::orderBy('Code', 'DESC')->get();

        return view('superadmin.category_type.index',$data);
    }

    public function category_type_add(Request $request)
    {
        return view('superadmin.category_type.create');
    }

    public function category_type_store(Request $request)
    {
        $check = CategoryType::where('CategoryType', $request->name)->exists();

        if($check){
            return back()->with('error', 'This Category type already exists.');
        }

        $params['CategoryType']    = $request->name;

        $res = CategoryType::create($params);

        if($res){
            return redirect()->route('category.type')->with('success', 'Data Store Successfully.'); 
        } else {
           return redirect()->route('category.type')->with('error', 'Try Again.');  
        }
    }

    public function category_type_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = CategoryType::where('Code', $id)->first();
        
        return view('superadmin.category_type.edit',$data);

    }

    public function category_type_update(Request $request)
    {
        $check = CategoryType::where('CategoryType', $request->name)->where('Code', '!=', $request->id)->exists();

        if($check){
            return back()->with('error', 'This Category type already exists.');
        }
        
        $params['CategoryType'] = $request->name;

        $res = CategoryType::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('category.type')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('category.type')->with('error', 'Try Again.');  
        }
    }

    public function category_type_delete($id)
    {
        $id = decrypt($id);

        CategoryType::where('Code', $id)->delete();
        
        return redirect()->route('category.type')->with('error', 'Delete Successfully.'); 

    }

    public function category_type_status($id)
    {
        $id = decrypt($id);

        $data = CategoryType::where('Code', $id)->first();

        if($data->status=='Active'){
            $params['status'] = 'Inactive';
        } else {
            $params['status'] = 'Active';
        }

        CategoryType::where('Code', $id)->update($params);
        
        return redirect()->route('category.type')->with('success', 'Status Change Successfully.'); 

    }


    public function room_category(Request $request)
    {
        $data['datas'] = RoomCategoryMaster::orderBy('id', 'DESC')->get();

        return view('superadmin.room_category.index',$data);
    }

    public function room_category_add(Request $request)
    {
        return view('superadmin.room_category.create');
    }

    public function room_category_store(Request $request)
    {
        $check = RoomCategoryMaster::where('name', $request->name)->exists();

        if($check){
            return back()->with('error', 'This Category type already exists.');
        }

        $params['name']        = $request->name;
        $params['no_of_rooms'] = $request->no_of_room;
        $params['description'] = $request->description;
        $params['gst']         = $request->gst;

        if( $request->hasFile('room_image')) {
            $image = $request->file('room_image');
            $path = public_path(). '/room_image/';
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move($path, $filename);

            $params['room_image'] = '/public/room_image/'.$filename;
        }

        $res = RoomCategoryMaster::create($params);

        if($res){
            return redirect()->route('room.category')->with('success', 'Data Store Successfully.'); 
        } else {
           return redirect()->route('room.category')->with('error', 'Try Again.');  
        }
    }

    public function room_category_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = RoomCategoryMaster::whereId($id)->first();
        
        return view('superadmin.room_category.edit',$data);

    }

    public function room_category_update(Request $request)
    {
        $check = RoomCategoryMaster::where('name', $request->name)->where('id', '!=', $request->id)->exists();

        if($check){
            return back()->with('error', 'This Category type already exists.');
        }
        
        $params['name']        = $request->name;
        $params['no_of_rooms'] = $request->no_of_room;
        $params['gst']         = $request->gst;
        $params['description'] = $request->description;

        if( $request->hasFile('room_image')) {
            $image = $request->file('room_image');
            $path = public_path(). '/room_image/';
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move($path, $filename);

            $params['room_image'] = '/public/room_image/'.$filename;
        }

        $res = RoomCategoryMaster::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('room.category')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('room.category')->with('error', 'Try Again.');  
        }
    }

    public function room_category_delete($id)
    {
        $id = decrypt($id);

        RoomCategoryMaster::whereId($id)->delete();
        
        return redirect()->route('room.category')->with('error', 'Delete Successfully.'); 

    }

    public function room_category_status($id)
    {
        $id = decrypt($id);

        $data = RoomCategoryMaster::whereId($id)->first();

        if($data->status=='Active'){
            $params['status'] = 'Inactive';
        } else {
            $params['status'] = 'Active';
        }

        RoomCategoryMaster::whereId($id)->update($params);
        
        return redirect()->route('room.category')->with('success', 'Status Change Successfully.'); 

    }


    public function room_charges_master(Request $request)
    {
        $data['datas'] = RoomChargesMaster::orderBy('id', 'DESC')->get();

        return view('superadmin.room_charges_master.index',$data);
    }

    public function room_charges_master_add(Request $request)
    {
        $data['catgeory']       = CategoryMaster::where('status', 'Active')->get();
        $data['catgeory_type']  = CategoryType::where('status', 'Active')->get();
        $data['occupants']      = OccupantMaster::where('status', 'Active')->get();
        $data['room_cates']     = RoomCategoryMaster::where('status', 'Active')->get();

        return view('superadmin.room_charges_master.create', $data);
    }

    public function room_charges_master_store(Request $request)
    {
        $check = RoomChargesMaster::where('room_category_id', $request->room_category_id)->where('category_id', $request->category_id)->where('category_type_id', $request->category_type_id)->where('occupant_type_id', $request->occupant_type_id)->exists();

        if($check){
            return back()->with('error', 'This Room Category already exists.');
        }
        
        $params['category_id']         = $request->category_id;
        $params['category_type_id']    = $request->category_type_id;
        $params['occupant_type_id']    = $request->occupant_type_id;
        $params['room_category_id']    = $request->room_category_id;
        $params['charges_nite']        = $request->charges;
        $params['no_of_booked_room']   = $request->no_of_booked_room;
        $params['max_no_of_nites']     = $request->max_no_of_nites;

        $res = RoomChargesMaster::create($params);

        if($res){
            return redirect()->route('room.charges.master')->with('success', 'Data Store Successfully.'); 
        } else {
           return redirect()->route('room.charges.master')->with('error', 'Try Again.');  
        }
    }

    public function room_charges_master_edit($id)
    {
        $id = decrypt($id);
        
        $data['data'] = RoomChargesMaster::whereId($id)->first();

        $data['catgeory']       = CategoryMaster::where('status', 'Active')->get();
        $data['catgeory_type']  = CategoryType::where('status', 'Active')->get();
        $data['occupants']      = OccupantMaster::where('status', 'Active')->get();
        $data['room_cates']     = RoomCategoryMaster::where('status', 'Active')->get();
        
        return view('superadmin.room_charges_master.edit',$data);

    }

    public function room_charges_master_update(Request $request)
    {
        $check = RoomChargesMaster::where('room_category_id', $request->room_category_id)->where('category_id', $request->category_id)->where('category_type_id', $request->category_type_id)->where('occupant_type_id', $request->occupant_type_id)->where('id', '!=', $request->id)->exists();

        if($check){
            return back()->with('error', 'This Room Category already exists.');
        }
        
        $params['category_id']         = $request->category_id;
        $params['category_type_id']    = $request->category_type_id;
        $params['occupant_type_id']    = $request->occupant_type_id;
        $params['room_category_id']    = $request->room_category_id;
        $params['charges_nite']        = $request->charges;
        $params['no_of_booked_room']   = $request->no_of_booked_room;
        $params['max_no_of_nites']     = $request->max_no_of_nites;

        $res = RoomChargesMaster::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('room.charges.master')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('room.charges.master')->with('error', 'Try Again.');  
        }
    }

    public function room_charges_master_delete($id)
    {
        $id = decrypt($id);

        RoomChargesMaster::whereId($id)->delete();
        
        return redirect()->route('room.charges.master')->with('error', 'Delete Successfully.'); 

    }

    public function room_charges_master_status($id)
    {
        $id = decrypt($id);

        $data = RoomChargesMaster::whereId($id)->first();

        if($data->status=='Active'){
            $params['status'] = 'Inactive';
        } else {
            $params['status'] = 'Active';
        }

        RoomChargesMaster::whereId($id)->update($params);
        
        return redirect()->route('room.charges.master')->with('success', 'Status Change Successfully.'); 

    }

    public function room_cancellation_policy(Request $request)
    {
        $data['datas'] = RoomCancellationPolicy::orderBy('id', 'DESC')->get();

        return view('superadmin.room_cancellation_policy.index',$data);
    }

    public function room_cancellation_policy_add(Request $request)
    {
        return view('superadmin.room_cancellation_policy.create');
    }

    public function room_cancellation_policy_store(Request $request)
    {
        $params['from_days']   = $request->from_days;
        $params['to_days']     = $request->to_days;
        $params['deduction']   = $request->deduction;
        $params['GST']         = $request->GST;

        $res = RoomCancellationPolicy::create($params);

        if($res){
            return redirect()->route('room.cancellation.policy')->with('success', 'Data Store Successfully.'); 
        } else {
           return redirect()->route('room.cancellation.policy')->with('error', 'Try Again.');  
        }
    }

    public function room_cancellation_policy_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = RoomCancellationPolicy::find($id);
        
        return view('superadmin.room_cancellation_policy.edit',$data);

    }

    public function room_cancellation_policy_update(Request $request)
    {
        $params['from_days']   = $request->from_days;
        $params['to_days']     = $request->to_days;
        $params['deduction']   = $request->deduction;
        $params['GST']         = $request->GST;

        $res = RoomCancellationPolicy::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('room.cancellation.policy')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('room.cancellation.policy')->with('error', 'Try Again.');  
        }
    }

    public function room_cancellation_policy_delete($id)
    {
        $id = decrypt($id);

        RoomCancellationPolicy::whereId($id)->delete();
        
        return redirect()->route('room.cancellation.policy')->with('error', 'Delete Successfully.'); 

    }




    public function room_block(Request $request)
    {
        $data['datas'] = BlockRoom::orderBy('id', 'DESC')->get();

        return view('superadmin.room_block.index',$data);
    }

    public function room_block_add(Request $request)
    {
        $data['rooms'] = RoomCategoryMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

        return view('superadmin.room_block.create', $data);
    }

    public function room_block_store(Request $request)
    {
        $params['from_date']            = $request->from_date.' '.env('CheckIn',null);
        $params['to_date']              = $request->to_date.' '.env('CheckOut',null);
        $params['room_category_id']     = $request->room_category_id;
        $params['blocked_room']         = $request->blocked_room;
        $params['remark']               = $request->remark;

        $res = BlockRoom::create($params);

        if($res){
            return redirect()->route('room.block')->with('success', 'Data Store Successfully.'); 
        } else {
           return redirect()->route('room.block')->with('error', 'Try Again.');  
        }
    }

    public function room_block_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = BlockRoom::find($id);

        $data['rooms'] = RoomCategoryMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();
        
        return view('superadmin.room_block.edit',$data);

    }

    public function room_block_update(Request $request)
    {
        $params['from_date']            = $request->from_date.' '.env('CheckIn',null);
        $params['to_date']              = $request->to_date.' '.env('CheckOut',null);
        $params['room_category_id']     = $request->room_category_id;
        $params['blocked_room']         = $request->blocked_room;
        $params['remark']               = $request->remark;

        $res = BlockRoom::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('room.block')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('room.block')->with('error', 'Try Again.');  
        }
    }

    public function room_block_delete($id)
    {
        $id = decrypt($id);

        BlockRoom::whereId($id)->delete();
        
        return redirect()->route('room.block')->with('error', 'Delete Successfully.'); 

    }



    public function SOP(Request $request)
    {
        if(Auth::check() && Auth::user()->role=='Room Manager'){
            $data['datas'] = SOP::where('type', 'Room Booking')->orderBy('id', 'DESC')->get();
        } elseif(Auth::check() && Auth::user()->role=='Banquet Manager'){
            $data['datas'] = SOP::where('type', 'Banquet Booking')->orderBy('id', 'DESC')->get();
        } else {
            return redirect()->back();
        }        

        return view('superadmin.SOP.index',$data);
    }

    public function SOP_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = SOP::find($id);
        
        return view('superadmin.SOP.edit',$data);

    }

    public function SOP_update(Request $request)
    {
        $params['content'] = $request->content;

        $res = SOP::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('SOP')->with('success', 'Data Update Successfully.'); 
        } else {
           return redirect()->route('SOP')->with('error', 'Try Again.');  
        }
    }



    public function room_bookings(Request $request)
    {

        $q = RoomBooking::query();            

        if($request->member_id){
            $q->where('memberID', $request->member_id);
        }
        
        if($request->status){
            $q->where('status', $request->status);
        }

        if($request->booking_no){
            $q->where('booking_number', $request->booking_no);
        }

        if($request->checkIn){
            $q->where('checkin', '>=', $request->checkIn.' '.env('CheckIn',null));
        }

        if($request->checkOut){
            $q->where('checkout', '<=', $request->checkOut.' '.env('CheckOut',null));
        }

        $data['datas'] = $q->orderBy('id', 'DESC')->get();

        $data['request'] = $request;
        
        return view('superadmin.booking.room_bookings', $data);
    }

    public function room_booking_details($id)
    {
        
        $id = decrypt($id);

        $room = RoomBooking::where('id', $id)->first();

        $data['datas'] = $room;

        $data['member'] = Member::where("MemberID",$room->memberID)->first();
        
        $data['data_items'] = RoomBookingItem::where('booking_id', $id)->get();

        $data['transaction'] = DB::table('transactions')->where('transID', $room->booking_number)->first();

        return view('superadmin.booking.room_booking_details', $data);
    }

    public function cancel_room_bookings(Request $request)
    {
        $q = RoomBooking::query();            

        if($request->member_id){
            $q->where('memberID', $request->member_id);
        }
        
        $q->where('status', 'Cancelled');

        if($request->booking_no){
            $q->where('booking_number', $request->booking_no);
        }

        if($request->checkIn){
            $q->where('checkin', '>=', $request->checkIn.' '.env('CheckIn',null));
        }

        if($request->checkOut){
            $q->where('checkout', '<=', $request->checkOut.' '.env('CheckOut',null));
        }

        $data['datas'] = $q->orderBy('id', 'DESC')->get();

        $data['request'] = $request;
        
        return view('superadmin.booking.cancel_room_bookings', $data);
    }

    public function staff_add(Request $request)
    {
        return view('superadmin.staff_add');
    }

    public function staff_store(Request $request)
    {
        $check = Member::where('MemberID', $request->MemberID)->exists();

        if($check){

            return redirect()->back()->with('error', 'This Login ID alredy exists.'); 

        } else {

            $params['MemberID']         = $request->MemberID;
            $params['DisplayName']      = $request->DisplayName;
            $params['role']             = $request->role;
            $params['Email']            = $request->Email;
            $params['Password']         = Hash::make($request->Password);
            $params['CategoryCode']     = '';
            $params['state']            = '0';
            $params['country']          = '0';
            $params['pin']              = '0';
            $params['SpouseName']       = '';
            $params['SpouseDOB']        = '';
            $params['AnniversaryDate']  = '';

            $res = Member::create($params);

            if($res){
                return redirect()->route('main.superadmin.dashboard')->with('success', 'Data Store Successfully.'); 
            } else {
               return redirect()->route('main.superadmin.dashboard')->with('error', 'Try Again.');  
            }

        }
        
    }

    public function staff_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = Member::find($id);
        
        return view('superadmin.staff_edit',$data);
    }

    public function staff_update(Request $request)
    {
        $check = Member::where('MemberID', $request->MemberID)->where('id', '!=', $request->id)->exists();

        if($check){

            return redirect()->back()->with('error', 'This Login ID alredy exists.'); 

        } else {

            $params['MemberID']         = $request->MemberID;
            $params['DisplayName']      = $request->DisplayName;
            $params['role']             = $request->role;
            $params['Email']            = $request->Email;

            $res = Member::whereId($request->id)->update($params);

            if($res){
                return redirect()->route('main.superadmin.dashboard')->with('success', 'Data Update Successfully.'); 
            } else {
               return redirect()->route('main.superadmin.dashboard')->with('error', 'Try Again.');  
            }

        }        
    }

    public function staff_delete($id)
    {
        $id = decrypt($id);

        Member::whereId($id)->delete();
        
        return redirect()->route('main.superadmin.dashboard')->with('error', 'Delete Successfully.'); 
    }

    public function feedback_enquiry(Request $request)
    {
        $data['datas'] = Feedback::orderBy('id', 'DESC')->get();
        
        return view('superadmin.feedback_enquiry.index',$data);
    }

    // Circulars Category Master
    public function circulars_category_master(Request $request)
    {
        $data['datas'] = CircularsCategory::orderBy('id', 'DESC')->get();

        return view('superadmin.circulars_category.index',$data);
    }

    public function circulars_category_master_add(Request $request)
    {
        return view('superadmin.circulars_category.create');
    }

    public function circulars_category_master_store(Request $request)
    {
        $params['name'] = $request->name;
        $params['status'] = 'Active';

        $res = CircularsCategory::create($params);

        if($res){
            return redirect()->route('circulars.category.master')->with('success', 'Category Store Successfully.'); 
        } else {
           return redirect()->route('circulars.category.master')->with('error', 'Try Again.');  
        }
    }

    public function circulars_category_master_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = CircularsCategory::find($id);
        
        return view('superadmin.circulars_category.edit',$data);
    }

    public function circulars_category_master_update(Request $request)
    {
        $params['name'] = $request->name;

        $res = CircularsCategory::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('circulars.category.master')->with('success', 'Category Update Successfully.'); 
        } else {
           return redirect()->route('circulars.category.master')->with('error', 'Try Again.');  
        }
    }

    public function circulars_category_master_delete($id)
    {
        $id = decrypt($id);

        CircularsCategory::whereId($id)->delete();
        
        return redirect()->route('circulars.category.master')->with('error', 'Delete Successfully.'); 
    }

    public function circulars_category_master_status($id)
    {
        $id = decrypt($id);

        $data = CircularsCategory::find($id);

        if($data->status=='Active'){
            $params['status'] = 'Inactive';
        } else {
            $params['status'] = 'Active';
        }

        CircularsCategory::whereId($id)->update($params);
        
        return redirect()->route('circulars.category.master')->with('success', 'Status Change Successfully.'); 
    }

    // News and Circulars
    public function news_circulars(Request $request)
    {
        $data['datas'] = Circular::with('category')->orderBy('id', 'DESC')->get();

        return view('superadmin.news_circulars.index',$data);
    }

    public function news_circulars_add(Request $request)
    {
        $data['categories'] = CircularsCategory::where('status', 'Active')->orderBy('name', 'ASC')->get();
        return view('superadmin.news_circulars.create',$data);
    }

    public function news_circulars_store(Request $request)
    {
        $params['category_id'] = $request->category_id;
        $params['name'] = $request->name;
        $params['status'] = 'Active';

        if($request->hasFile('document')){
            $file = $request->file('document');
            $filename = time().'_'.rand(1000,9999).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('circulars'), $filename);
            $params['document'] = $filename;
        }

        $res = Circular::create($params);

        if($res){
            return redirect()->route('news.circulars')->with('success', 'Circular Store Successfully.'); 
        } else {
           return redirect()->route('news.circulars')->with('error', 'Try Again.');  
        }
    }

    public function news_circulars_edit($id)
    {
        $id = decrypt($id);

        $data['data'] = Circular::find($id);
        $data['categories'] = CircularsCategory::where('status', 'Active')->orderBy('name', 'ASC')->get();
        
        return view('superadmin.news_circulars.edit',$data);
    }

    public function news_circulars_update(Request $request)
    {
        $params['category_id'] = $request->category_id;
        $params['name'] = $request->name;

        if($request->hasFile('document')){
            // Delete old document if exists
            $circular = Circular::find($request->id);
            if($circular && $circular->document && file_exists(public_path('circulars/'.$circular->document))){
                unlink(public_path('circulars/'.$circular->document));
            }

            $file = $request->file('document');
            $filename = time().'_'.rand(1000,9999).'.'.$file->getClientOriginalExtension();
            $file->move(public_path('circulars'), $filename);
            $params['document'] = $filename;
        }

        $res = Circular::whereId($request->id)->update($params);

        if($res){
            return redirect()->route('news.circulars')->with('success', 'Circular Update Successfully.'); 
        } else {
           return redirect()->route('news.circulars')->with('error', 'Try Again.');  
        }
    }

    public function news_circulars_delete($id)
    {
        $id = decrypt($id);

        $circular = Circular::find($id);
        if($circular && $circular->document && file_exists(public_path('circulars/'.$circular->document))){
            unlink(public_path('circulars/'.$circular->document));
        }

        Circular::whereId($id)->delete();
        
        return redirect()->route('news.circulars')->with('error', 'Delete Successfully.'); 
    }

    public function news_circulars_status($id)
    {
        $id = decrypt($id);

        $data = Circular::find($id);

        if($data->status=='Active'){
            $params['status'] = 'Inactive';
        } else {
            $params['status'] = 'Active';
        }

        Circular::whereId($id)->update($params);
        
        return redirect()->route('news.circulars')->with('success', 'Status Change Successfully.'); 
    }

}





