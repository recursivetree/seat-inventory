<?php

namespace RecursiveTree\Seat\Inventory\Http\Controllers;

use RecursiveTree\Seat\Inventory\Helpers\FittingPluginHelper;
use RecursiveTree\Seat\Inventory\Helpers\LocationHelper;
use RecursiveTree\Seat\Inventory\Helpers\StockHelper;
use RecursiveTree\Seat\Inventory\Models\Location;
use RecursiveTree\Seat\Inventory\Models\Stock;
use RecursiveTree\Seat\Inventory\Models\StockCategory;

use Seat\Web\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class InventoryController extends Controller
{
    private function redirectWithStatus($request,$redirect,$message,$type){
        $request->session()->flash('message', [
            'message' => $message,
            'type' => $type
        ]);
        return redirect()->route($redirect);
    }

    public function dashboard(Request $request){


        return view("inventory::dashboard");
    }

    public function getCategories(Request $request){
        $request->validate([
            'location' =>'nullable|integer'
        ]);

        $categories = StockCategory::with("stocks","stocks.location")->get();

        $location_id = $request->location;
        if($location_id) { // 0 stands for all locations
            $categories = $categories->filter(function ($category) use ($location_id) {
                return $category->stocks()->where("location_id", $location_id)->exists();
            });
        }

        return response()->json($categories->values());
    }

    public function mainFilterLocationSuggestions(Request $request){
        $request->validate([
            "term"=>"nullable|string"
        ]);

        if($request->term==null){
            $locations = Location::all();
        } else {
            $locations = Location::where("name","like","%$request->term%")->get();
        }

        $suggestions = $locations->map(function ($location){
            return [
                'id' => $location->id,
                'text' => $location->name
            ];
        });

        return response()->json([
            'results'=>$suggestions
        ]);
    }

    public function addStockSuggestion(Request $request){
        $request->validate([
            "term"=>"nullable|string",
        ]);

        $location_ids = null;
        if($request->term){
            $location_ids = Location::where("name","like","%$request->term%")->pluck("id");
        }

        $query = Stock::query();
        if($request->term){
            $query->where("name", "like", "%$request->term%");
        }
        if($location_ids) {
            $query->orWhereIn("location_id", $location_ids);
        }
        $stocks = $query->get();

        $suggestions = $stocks
            ->map(function ($stock){
                $location = $stock->location->name;
                return [
                    'id' => $stock,
                    'text' => "$stock->name --- $location"
                ];
            });

        return response()->json([
            'results'=>$suggestions
        ]);
    }

    public function saveCategory(Request $request){
        $request->validate([
            "name" => "required|string|max:64",
            "id" => "nullable|integer",
            "stocks" => "required|array",
            "stocks.*" => "integer"
        ]);

        $category = StockCategory::find($request->id);
        if(!$category){
            $category = new StockCategory();
        }

        foreach ($request->stocks as $id){
            $stock = Stock::find($id);
            if(!$stock) {
                return response()->json([],400);
            }
        }

        $category->stocks()->sync($request->stocks);
        $category->name = $request->name;

        $category->save();

        return response()->json();
    }

    public function deleteCategory(Request $request){
        $request->validate([
            "id" => "required|integer"
        ]);

        $category = StockCategory::find($request->id);
        if(!$category){
            return $this->redirectWithStatus($request,'inventory.main',"Could not find category!", 'error');
        }

        //remove all linked stocks
        $category->stocks()->detach();
        //actually delete it
        StockCategory::destroy($request->id);

        return response()->json();
    }

    public function removeStockFromCategory(Request $request){
        $request->validate([
            "stock"=>"required|integer",
            "category"=>"required|integer"
        ]);

        $category = StockCategory::find($request->category);
        if(!$category){
            return response()->json([],400);
        }

        $category->stocks()->detach($request->stock);

        //go back
        return response()->json();
    }


    public function about(){
        return view("inventory::about");
    }

    public function stockIcon($id){
        $stock = Stock::findOrFail($id);

        return Image::make($stock->getIcon())->response();
    }
}