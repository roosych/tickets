<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;

class CurrentDepartmentController extends Controller
{
    /**
     * Переключение текущего департамента (для менеджеров нескольких отделов).
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $id = (int) $request->input('department_id');

        abort_unless(in_array($id, $user->accessibleDepartmentIds(), true), 403);

        $request->session()->put(User::CURRENT_DEPARTMENT_SESSION_KEY, $id);

        // Отдел, который не исполняет тикеты, не имеет раздела «Мой отдел» —
        // страницы отдела отдали бы 404, поэтому ведём на главную
        if (! Department::whereKey($id)->value('active')) {
            return redirect()->route('cabinet.index');
        }

        // Возвращаем на ту же страницу, но не на страницу конкретного тикета —
        // он может относиться к прежнему отделу
        $back = url()->previous();
        $isTicketPage = preg_match('#/cabinet/tickets/\d+#', parse_url($back, PHP_URL_PATH) ?? '');

        return $isTicketPage ? redirect()->route('cabinet.tickets.index') : redirect()->to($back);
    }
}
