import React from 'react';
import { usePage } from '@inertiajs/react'; // Importa usePage para acceder a los props globales
import NavigationAdmin from '../Components/NavigationAdmin';

export default function AdminDashboard() {
    // Acceder a los datos del usuario actual desde Inertia
    const { user } = usePage().props;

    return (
        <div>
            {/* Navegación del Admin */}
            <NavigationAdmin admin={true} />

            <div className="container mx-auto mt-12">
                {/* Muestra el nombre del usuario autenticado */}
                <h1 className="text-4xl font-bold">
                    Panel de Control del Administrador de {user.nombre}
                </h1>
                {/* Aquí va el contenido del panel del admin */}
            </div>
        </div>
    );
}
