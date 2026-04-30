<?php 
namespace App\Http\Controllers\Admin\Tee;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;

use App\Models\RentalClub;

use Illuminate\Http\Request;



class RentalClubController extends Controller
{
   
    public function index()
    {
        $rentalClubs = RentalClub::all();
        return view('admin.tee.service_manage', compact('rentalClubs'));
    }

  
    public function create()
    {
        return view('admin.tee.rental_clubs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);
       $request = Helpers::set_common_request($request);
        RentalClub::create($request->all());

        return redirect()->route('service_manage')->with('success', 'Rental Club created successfully!');
    }

  
    public function edit(RentalClub $rentalClub)
    {
        return view('admin.tee.rental_clubs.edit', compact('rentalClub'));
    }

    public function update(Request $request, RentalClub $rentalClub)
    {
        $request->validate([
            'name' => 'required|string',
        ]);
        $request = Helpers::set_common_request($request);
        $rentalClub->update($request->all());

        return redirect()->route('service_manage')->with('success', 'Rental Club updated successfully!');
    }


    public function destroy(RentalClub $rentalClub)
    {
        $rentalClub->delete();

        return redirect()->route('service_manage')->with('success', 'Rental Club deleted successfully!');
    }

    public function status_update(Request $request)
    {
        $tableData= RentalClub::find($request['id']);
        $tableData->is_active = $request['status'];

        if($tableData->save()){
            $success = 1;
        }else{
            $success = 0;
        }
        return response()->json([
            'success' => $success,
        ], 200);
    }
}

?>