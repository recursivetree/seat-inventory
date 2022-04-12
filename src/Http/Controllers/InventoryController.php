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

    public function main(Request $request){

        $request->validate([
            'location_filter' =>'nullable|integer'
        ]);

        $categories = StockCategory::with("stocks")->get();

        $location_id = $request->location_filter;
        if($location_id) { // 0 stands for all locations
            $categories = $categories->filter(function ($category) use ($location_id) {
                return $category->stocks()->where("location_id", $location_id)->exists();
            });

            $location = Location::find($location_id);
        } else {
            $location = new Location();
            $location->name = "All Categories";
            $location->id = 0;
        }

        $has_fitting_plugin = FittingPluginHelper::pluginIsAvailable();

        return view("inventory::main",compact("categories","location", "has_fitting_plugin"));
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

        $suggestions = $suggestions->push([
            'id' => 0,
            'text' => "All Categories"
        ]);

        return response()->json([
            'results'=>$suggestions
        ]);
    }

    public function mainEditCategoryAddStockSuggestion(Request $request){
        $request->validate([
            "term"=>"nullable|string",
            "category"=>"nullable|integer"
        ]);

        $location_ids = null;
        if($request->term){
            $location_ids = Location::where("name","like","%$request->term%")->pluck("id");
        }

        if($request->category) {
            $query = Stock::whereDoesntHave("categories", function ($query) use ($request) {
                $query->where("recursive_tree_seat_inventory_stock_categories.id", $request->category);
            });
        } else {
            $query = Stock::query();
        }
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
                    'id' => $stock->id,
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
            "id" => "nullable|integer"
        ]);

        $category = StockCategory::find($request->id);
        if(!$category){
            $category = new StockCategory();
        }

        $category->name = $request->name;

        $category->save();

        return $this->redirectWithStatus($request,'inventory.main',"Successfully modified category!", 'success');
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

        //go back
        return $this->redirectWithStatus($request,'inventory.main',"Successfully deleted category!", 'success');
    }

    public function addStockToCategory(Request $request){
        $request->validate([
            "stock"=>"required|integer",
            "category"=>"required|integer"
        ]);

        $category = StockCategory::find($request->category);
        if(!$category){
            return $this->redirectWithStatus($request,'inventory.main',"Could not find category!", 'error');
        }

        $stock = Stock::find($request->stock);
        if(!$stock){
            return $this->redirectWithStatus($request,'inventory.main',"Could not find stock!", 'error');
        }

        $category->stocks()->attach($stock->id);

        //go back
        return $this->redirectWithStatus($request,'inventory.main',"Successfully added stock!", 'success');
    }

    public function removeStockFromCategory(Request $request){
        $request->validate([
            "stock"=>"required|integer",
            "category"=>"required|integer"
        ]);

        $category = StockCategory::find($request->category);
        if(!$category){
            return $this->redirectWithStatus($request,'inventory.main',"Could not find category!", 'error');
        }

        $category->stocks()->detach($request->stock);

        //go back
        return $this->redirectWithStatus($request,'inventory.main',"Successfully removed stock!", 'success');
    }


    public function about(){
        return view("inventory::about");
    }

    public function stockIcon($id){
        $stock = Stock::findOrFail($id);

        return Image::make($stock->getIcon())->response();
    }
}