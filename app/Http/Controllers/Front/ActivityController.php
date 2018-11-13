<?php
namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\UserActivitiesAttributes;
use App\Model\UserActivities;
use App\Model\Activity;
use Response;
use Auth;

class ActivityController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('front.activity.angular');
    }

    public function add(Request $request)
    {
        $activity = Activity::find($request->input('activity_id'));

        $count = $request->input('count', 1);

        for ($i = 0; $i < $count; $i++) {
            self::saveUserActivity($request->all(), $activity->id);
        }

        AchievementController::processAchievements($request);
        return Response::json(['success' => true]);
    }

    public static function saveUserActivity($params, $activity_id)
    {
        $user_sport = new UserActivities();
        $user_sport->user_id = Auth::user()->id;
        $user_sport->activity_id = $activity_id;
        $user_sport->activity_type = 1;
        if ($date = array_get($params, 'date')) {
            $user_sport->created_at = strtotime($date);
        }

        $user_sport->save();

        if ($new_attributes = array_get($params, 'new_attributes')) {
            $attributes_values = array_get($params, 'attributes_values');
            $attributes = [];
            foreach ($new_attributes as $key => $value) {
                $attributes[$value] = $attributes_values[$key];
            }
        } else {
            $attributes = array_get($params, 'attributes');
        }

        if ($attributes && !empty($attributes)) {
            foreach ($attributes as $id_attribute => $value) {
                if (empty($value))
                    continue;

                UserActivitiesAttributes::create([
                    'user_activity_id' => $user_sport->id,
                    'attribute_id' => $id_attribute,
                    'attribute_value' => $value,
                ]);
            }
        }
    }

    public function autocomplete(Request $request)
    {
        $name = $request->input('keyword');
        $result = [];

        $activities = Activity::findByKeyword($name)->get()->groupBy('type.name')->toArray();
        foreach ($activities as $key => $activity) {
            $result[] = [
                'type' => $key,
                'records' => $activity,
            ];
        }

        return Response::json($result);
    }
}
