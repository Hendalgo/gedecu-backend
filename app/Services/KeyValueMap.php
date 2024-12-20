<?php

namespace App\Services;

class KeyValueMap
{
    public function transformElement($items)
    {
        if ($items instanceof \Illuminate\Database\Eloquent\Model) {
            $items = collect([$items]);
        }
        $items->each(function ($item) {
            $data = [];
            foreach ($item->data as $item_data) {
                $data[$item_data->key] = $item_data->value;
            }
            $item->sdata = $data;

            unset($item->data);

            $item->data = $item->sdata;

            unset($item->sdata);
        });

        return $items;
    }

    public function transformElementWithoutInstance($items)
    {
        $items->each(function ($item) {
            $data = [];
            foreach ($item->data as $item_data) {
                $data[$item_data->key] = $item_data->value;
            }
            $item->sdata = $data;

            unset($item->data);

            $item->data = $item->sdata;

            unset($item->sdata);
        });

        return $items;
    }
}
