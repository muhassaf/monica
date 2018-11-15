<?php

namespace App\Http\Controllers\Contacts;

use App\Helpers\DateHelper;
use App\Models\Contact\Task;
use App\Models\Contact\Contact;
use App\Services\Task\CreateTask;
use App\Http\Controllers\Controller;
use App\Http\Requests\People\TasksRequest;
use App\Http\Requests\People\TaskToggleRequest;

class TasksController extends Controller
{
    /**
     * Get all the tasks of this contact.
     */
    public function index(Contact $contact)
    {
        $tasks = collect([]);

        foreach ($contact->tasks as $task) {
            $data = [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'completed' => $task->completed,
                'completed_at' => DateHelper::getShortDate($task->completed_at),
                'edit' => false,
            ];
            $tasks->push($data);
        }

        return $tasks;
    }

    /**
     * Store the task.
     */
    public function store(TasksRequest $request, Contact $contact)
    {
        return (new CreateTask)->execute([
            'account_id' => auth()->user()->account->id,
            'contact_id' => $contact->id,
            'title' => $request->get('title'),
            'description' => ($request->get('description') == '' ? null : $request->get('description')),
        ]);
    }

    /**
     * Edit the task field.
     */
    public function update(TasksRequest $request, Contact $contact, Task $task)
    {
        $task->update([
            'title' => $request->get('title'),
            'description' => ($request->get('description') == '' ? null : $request->get('description')),
            'completed' => $request->get('completed'),
        ]);

        return $task;
    }

    public function toggle(TaskToggleRequest $request, Contact $contact, Task $task)
    {
        // check if the state of the task has changed
        if ($task->completed) {
            $task->completed_at = null;
            $task->completed = false;
        } else {
            $task->completed = true;
            $task->completed_at = now();
        }

        $task->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Contact $contact
     * @param Task $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Contact $contact, Task $task)
    {
        $task->delete();
    }
}
