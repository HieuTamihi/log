<?php

namespace App\Services;

use App\Models\Machine;
use App\Models\Subsystem;
use App\Models\Component;
use App\Models\Upgrade;
use Illuminate\Support\Collection;

class SearchService
{
    /**
     * Search across all entities
     *
     * @param string $query
     * @return array
     */
    public function search(string $query): array
    {
        if (empty(trim($query))) {
            return [
                'machines' => collect(),
                'subsystems' => collect(),
                'components' => collect(),
                'upgrades' => collect(),
                'total' => 0,
            ];
        }

        $machines = $this->searchMachines($query);
        $subsystems = $this->searchSubsystems($query);
        $components = $this->searchComponents($query);
        $upgrades = $this->searchUpgrades($query);

        return [
            'machines' => $machines,
            'subsystems' => $subsystems,
            'components' => $components,
            'upgrades' => $upgrades,
            'total' => $machines->count() + $subsystems->count() + $components->count() + $upgrades->count(),
            'query' => $query,
        ];
    }

    /**
     * Search machines by name and description
     *
     * @param string $query
     * @return Collection
     */
    public function searchMachines(string $query): Collection
    {
        return Machine::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->with('subsystems.components')
            ->get();
    }

    /**
     * Search subsystems by name and description
     *
     * @param string $query
     * @return Collection
     */
    public function searchSubsystems(string $query): Collection
    {
        return Subsystem::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->with('machine', 'components')
            ->get();
    }

    /**
     * Search components by name and description
     *
     * @param string $query
     * @return Collection
     */
    public function searchComponents(string $query): Collection
    {
        return Component::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->with('subsystem.machine', 'upgrades')
            ->get();
    }

    /**
     * Search upgrades by name, purpose, trigger, steps, and definition_of_done
     *
     * @param string $query
     * @return Collection
     */
    public function searchUpgrades(string $query): Collection
    {
        return Upgrade::where('name', 'LIKE', "%{$query}%")
            ->orWhere('purpose', 'LIKE', "%{$query}%")
            ->orWhere('trigger', 'LIKE', "%{$query}%")
            ->orWhere('steps', 'LIKE', "%{$query}%")
            ->orWhere('definition_of_done', 'LIKE', "%{$query}%")
            ->with('component.subsystem.machine')
            ->get();
    }
}
