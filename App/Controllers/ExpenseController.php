<?php

namespace App\Controllers;

use App\Models\Expense;
use Core\Request;
use Core\Response;

class ExpenseController
{
    public function __construct(
        public Request $request
    )
    {
    }

    public function index()
    {
        Response::json(Expense::all());
    }

    public function show(int $id)
    {
        Response::json(
            Expense::findOrFail($id)
        );
    }

    public function store()
    {
        $status = Expense::create($this->request->all());

        $notification = ['title' => 'Позиция добавлена', 'type' => 'success'];

        if (!$status) {
            $notification = ['title' => 'Ошибка добавления позиции', 'type' => 'error'];
        }

        Response::json([
            'status' => $status,
            'notification' => $notification
        ]);
    }

    public function update(int $id)
    {
        Response::json([
            'status' => Expense::update($id, $this->request->all()),
            'notification' => ['title' => 'Изменения сохранены', 'type' => 'success']
        ]);
    }

    public function destroy(int $id)
    {
        $expense = Expense::findOrFail($id);

        Response::json([
            'status' => $expense->delete(),
            'notification' => ['title' => 'Позиция удалена', 'type' => 'success']
        ]);
    }
}
