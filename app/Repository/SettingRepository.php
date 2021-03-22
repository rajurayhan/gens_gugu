<?php

namespace App\Repository;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingRepository
{

    private $data;

    public function __construct()
    {
        $this->data = $this->all();
    }

    public function value($key, $default = null)
    {
        return ( $data = $this->get($key) ) ? $data->value : $default;
    }

    public function get($key)
    {
        return $this->data->get($key);
    }

    public function whereNotIn($list)
    {
        return $this->data->whereNotIn('key', $list);
    }

    public function all($user = null)
    {
        if (!$this->data) {
            $user = $user ?: auth()->id();
            $query = Setting::query();
            if ($user) {
                $query->select('settings.id', 'setting_user.id as pivot_id', 'settings.name', 'settings.key', DB::raw('coalesce( `pcdptc_setting_user`.`value`, `pcdptc_settings`.`value` ) as `value`'));
                $query->leftJoin('setting_user', function ($join) use ($user) {
                    $join->on('setting_user.setting_id', '=', 'settings.id');
                    $join->on('setting_user.user_id', '=', DB::raw($user));
                });
            } else {
                $query->select('settings.id', DB::raw('null as `pivot_id`'), 'settings.name', 'settings.key', 'settings.value');
            }
            $this->data = $query->get()->keyBy('key');
        }
        return $this->data;
    }
}
