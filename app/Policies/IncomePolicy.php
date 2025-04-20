<?php

namespace App\Policies;

use App\Abstract\GlobalPolicyAbstract;
use App\Models\Income;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Auth;

class IncomePolicy extends GlobalPolicyAbstract{
    /**
     * Permet d'indiquer quelle classe on teste, optionel mais fortement conseillé, sinon quand on utilise une classe GlobalPolicyAbstract, on doit renseigner quel Modèle on teste
     */
    protected $_forClass = "Income";
    /**
     * Raccourcis qui donne l'id de l'admin dans la table roles, évite une requête SQL quand il est renseigné
     */
    private int $idRoleAdmin = 1;
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(): bool{
        return $this->canReadAll();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Income $income): bool{
        return Auth::id() == $income->user_id || $this->canReadAll();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(): bool{
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Income $income): bool{
        return Auth::id() == $income->user_id || $this->canWriteAll();

    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Income $income): bool{
        return Auth::id() == $income->user_id || $this->canDeleteAll();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Income $income): bool{
        return Auth::role()==1;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Income $income): bool{
        return Auth::role()==1;
    }

    public function isAdmin(): bool{
        return Auth::user()->role == $this->idRoleAdmin;
    }
}
