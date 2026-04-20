<x-app-layout>
    <div
        data-vue="DashboardHome"
        data-props='@json(["stats" => $stats, "appointments" => $appointments])'>
    </div>
</x-app-layout>
