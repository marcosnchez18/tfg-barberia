import React, { useState } from 'react';
import { Inertia } from '@inertiajs/inertia';
import Swal from 'sweetalert2';
import NavigationAdmin from '../Components/NavigationAdmin';
import SobreNosotros from '@/Components/Sobrenosotros';
import Footer from '../Components/Footer';

export default function EditarServicios({ servicios }) {
    const [searchTerm, setSearchTerm] = useState('');
    const [currentService, setCurrentService] = useState(null);
    const [step, setStep] = useState(1);

    const eliminarServicio = (id) => {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¡Esta acción no se puede deshacer!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                Inertia.delete(route('servicios.destroy', id), {
                    onSuccess: () => {
                        Swal.fire('Eliminado', 'Servicio eliminado con éxito.', 'success');
                    },
                });
            }
        });
    };

    const editarServicio = (servicio) => {
        setCurrentService(servicio);
        setStep(1);
    };

    const handleSaveChanges = () => {
        Inertia.patch(route('servicios.update', currentService.id), {
            nombre: currentService.nombre,
            descripcion: currentService.descripcion,
            precio: currentService.precio,
            duracion: currentService.duracion
        }, {
            onSuccess: () => {
                Swal.fire('Guardado', 'Servicio actualizado con éxito.', 'success');
                setCurrentService(null);
            },
            onError: () => {
                Swal.fire('Error', 'Hubo un problema al actualizar el servicio', 'error');
            }
        });
    };

    const handleInputChange = (e) => {
        setCurrentService({
            ...currentService,
            [e.target.name]: e.target.value
        });
    };

    const filteredServicios = servicios.filter(servicio =>
        servicio.nombre.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div
            style={{
                backgroundImage: `url('/images/barberia.jpg')`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                minHeight: '100vh',

            }}
        >
            <NavigationAdmin />
            <br /><br /><br />
            <div className="clientes-admin-container p-6 rounded-lg" style={{ backgroundColor: 'rgba(23, 23, 23, 0.8)' }}>
                <h2 className="text-4xl font-bold mb-8 text-center text-white">Gestión de Servicios</h2>

                <div className="clientes-admin-search mb-4">
                    <input
                        type="text"
                        placeholder="Buscar por nombre de servicio..."
                        value={searchTerm}
                        onChange={(e) => setSearchTerm(e.target.value)}
                        className="clientes-admin-search-input w-full p-2 border rounded"
                    />
                </div>

                <div className="clientes-admin-table-container">
                    <table className="clientes-admin-table w-full">
                        <thead>
                            <tr>
                                <th className="clientes-admin-table-header">Nombre</th>
                                <th className="clientes-admin-table-header">Descripción</th>
                                <th className="clientes-admin-table-header">Precio</th>
                                <th className="clientes-admin-table-header">Duración</th>
                                <th className="clientes-admin-table-header text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {filteredServicios.map((servicio) => (
                                <tr key={servicio.id} className="clientes-admin-table-row">
                                    <td className="clientes-admin-table-cell">{servicio.nombre}</td>
                                    <td className="clientes-admin-table-cell">{servicio.descripcion}</td>
                                    <td className="clientes-admin-table-cell">{servicio.precio} €</td>
                                    <td className="clientes-admin-table-cell">{servicio.duracion} min</td>
                                    <td className="clientes-admin-table-cell text-center space-x-2">
                                        <button
                                            onClick={() => editarServicio(servicio)}
                                            className="clientes-admin-btn-edit"
                                        >
                                            ✏️
                                        </button>
                                        <button
                                            onClick={() => eliminarServicio(servicio.id)}
                                            className="clientes-admin-btn-delete"
                                        >
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* Modal de Edición */}
                {currentService && (
                    <div className="clientes-admin-edit-modal mt-8">
                        <h3 className="text-2xl font-bold mb-4 text-white">Editar Servicio</h3>
                        <div className="bg-gray-800 p-6 rounded-lg">
                            {step === 1 && (
                                <div>
                                    <label className="block text-white">Nombre:</label>
                                    <input
                                        type="text"
                                        name="nombre"
                                        value={currentService.nombre}
                                        onChange={handleInputChange}
                                        className="w-full p-2 border rounded mb-4 bg-gray-100"
                                    />
                                    <button onClick={() => setStep(2)} className="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">Siguiente</button>
                                </div>
                            )}
                            {step === 2 && (
                                <div>
                                    <label className="block text-white">Descripción:</label>
                                    <textarea
                                        name="descripcion"
                                        value={currentService.descripcion}
                                        onChange={handleInputChange}
                                        className="w-full p-2 border rounded mb-4 bg-gray-100"
                                    ></textarea>
                                    <button onClick={() => setStep(3)} className="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">Siguiente</button>
                                    <button onClick={() => setStep(1)} className="px-4 py-2 text-white bg-gray-500 rounded hover:bg-gray-600 ml-2">Anterior</button>
                                </div>
                            )}
                            {step === 3 && (
                                <div>
                                    <label className="block text-white">Precio (€):</label>
                                    <input
                                        type="number"
                                        name="precio"
                                        value={currentService.precio}
                                        onChange={handleInputChange}
                                        className="w-full p-2 border rounded mb-4 bg-gray-100"
                                    />
                                    <button onClick={() => setStep(4)} className="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">Siguiente</button>
                                    <button onClick={() => setStep(2)} className="px-4 py-2 text-white bg-gray-500 rounded hover:bg-gray-600 ml-2">Anterior</button>
                                </div>
                            )}
                            {step === 4 && (
                                <div>
                                    <label className="block text-white">Duración (minutos):</label>
                                    <input
                                        type="number"
                                        name="duracion"
                                        value={currentService.duracion}
                                        onChange={handleInputChange}
                                        className="w-full p-2 border rounded mb-4 bg-gray-100"
                                    />
                                    <button onClick={handleSaveChanges} className="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Guardar Cambios</button>
                                    <button onClick={() => setStep(3)} className="px-4 py-2 text-white bg-gray-500 rounded hover:bg-gray-600 ml-2">Anterior</button>
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>
            <SobreNosotros />
            <Footer />
        </div>
    );
}
