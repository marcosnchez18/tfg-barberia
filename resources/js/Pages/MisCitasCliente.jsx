import React, { useState } from 'react';
import { usePage } from '@inertiajs/react';
import Swal from 'sweetalert2';
import axios from 'axios';
import Calendar from 'react-calendar';
import 'react-calendar/dist/Calendar.css';
import dayjs from 'dayjs';
import 'dayjs/locale/es';
import Holidays from 'date-holidays';
import NavigationCliente from '../Components/NavigationCliente';
import WhatsAppButton from '@/Components/Wasa';
import SobreNosotros from '../Components/Sobrenosotros';
import Footer from '../Components/Footer';

dayjs.locale('es');

export default function MisCitasCliente() {
    const { citas = [], servicios = [] } = usePage().props;
    const [showModificar, setShowModificar] = useState(false);
    const [selectedCita, setSelectedCita] = useState(null);
    const [selectedServicio, setSelectedServicio] = useState(null);
    const [selectedDate, setSelectedDate] = useState(null);
    const [horariosDisponibles, setHorariosDisponibles] = useState([]);
    const today = dayjs().startOf('day');
    const holidays = new Holidays('ES', 'AN', 'CA');

    const citasOrdenadas = citas
        .filter(cita => dayjs(cita.fecha_hora_cita).isAfter(today) && cita.estado === 'pendiente')
        .sort((a, b) => new Date(a.fecha_hora_cita) - new Date(b.fecha_hora_cita));

    const citasCompletadas = citas
        .filter(cita =>
            cita.estado === 'completada' ||
            cita.estado === 'ausente' ||
            dayjs(cita.fecha_hora_cita).isBefore(today)
        )
        .sort((a, b) => new Date(a.fecha_hora_cita) - new Date(b.fecha_hora_cita));

    const getEstadoClass = (estado) => {
        switch (estado) {
            case 'pendiente':
                return 'text-blue-500';
            case 'completada':
                return 'text-green-500';
            case 'ausente':
                return 'text-red-500';
            default:
                return 'text-gray-500';
        }
    };

    const cancelarCita = (id, metodoPago) => {
        Swal.fire({
            title: '¿Estás seguro?',
            text: '¿Deseas cancelar esta cita? Si es así, ¿puedes darnos una breve explicación?',
            input: 'textarea',
            inputPlaceholder: 'Escribe tu explicación aquí...',
            showCancelButton: true,
            confirmButtonText: 'Sí, cancelar',
            cancelButtonText: 'No, mantener',
        }).then((result) => {
            if (result.isConfirmed) {
                axios
                    .delete(`/citas/${id}/cancelar`, {
                        data: { mensajeExplicacion: result.value }
                    })
                    .then(() => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Cita cancelada exitosamente',
                            text: metodoPago === 'adelantado'
                                ? 'Se ha solicitado la devolución del importe, que será ingresado en tu cuenta de PayPal de 3 a 5 días laborables.'
                                : 'Se ha cancelado su cita, hasta pronto.',
                            showConfirmButton: true,
                        }).then(() => {
                            window.location.reload();
                        });
                    })
                    .catch((error) => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: error.response?.data?.error || 'Ocurrió un error al cancelar la cita',
                        });
                    });
            }
        });
    };




    const handleModifyClick = (cita) => {
        setSelectedCita(cita);
        setSelectedServicio(cita.servicio);
        setShowModificar(true);
    };

    const handleSelectServicio = (servicio) => {
        setSelectedServicio(servicio);
    };

    const handleSelectDate = (date) => {
        setSelectedDate(date);
        const dayOfWeek = dayjs(date).day();
        const isHoliday = holidays.isHoliday(date);
        let horarios = [];

        if (dayjs(date).isSame(today, 'day') || isHoliday) {
            setHorariosDisponibles([]);
            return;
        } else if (dayOfWeek === 6) {
            horarios = ["10:00", "10:45", "11:30", "12:15", "13:00"];
        } else if (dayOfWeek === 0) {
            setHorariosDisponibles([]);
            return;
        } else {
            horarios = [
                "10:00", "10:45", "11:30", "12:15", "13:00",
                "16:00", "16:45", "17:30", "18:15", "19:00", "19:45", "20:30"
            ];
        }

        const formattedDate = dayjs(date).format('YYYY-MM-DD');
        axios.get(`/api/citas/horas-reservadas`, {
            params: { fecha: formattedDate, barbero_id: selectedCita.barbero.id }
        })
        .then(response => {
            const reservedTimes = response.data;
            const availableTimes = horarios.filter(hora => !reservedTimes.includes(hora));
            setHorariosDisponibles(availableTimes);
        })
        .catch(error => console.error("Error al obtener disponibilidad:", error));
    };

    const handleConfirmModification = (horario) => {
        if (!selectedServicio || !selectedDate || !selectedCita) {
            Swal.fire('Error', 'Por favor, selecciona un servicio, día y cita antes de confirmar.', 'error');
            return;
        }

        const fechaHoraCita = `${dayjs(selectedDate).format('YYYY-MM-DD')} ${horario}`;
        axios.patch(`/citas/${selectedCita.id}/modificar`, {
            servicio_id: selectedServicio.id,
            fecha_hora_cita: fechaHoraCita,
        })
        .then(() => {
            Swal.fire({
                title: 'Cita modificada',
                text: 'Tu cita ha sido modificada con éxito.',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => window.location.reload());
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: error.response?.data?.error || 'Ocurrió un error al modificar la cita',
            });
        });
    };

    const calificarCita = (citaId, valor) => {
        axios.patch(`/citas/${citaId}/calificar`, { valoracion: valor })
            .then(() => {
                Swal.fire({
                    title: 'Valoración guardada',
                    text: `Has valorado esta cita con ${valor} estrellas.`,
                    icon: 'success',
                    confirmButtonText: 'Aceptar'
                }).then(() => window.location.reload());
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.response?.data?.error || 'Ocurrió un error al guardar la valoración',
                });
            });
    };

    const StarRating = ({ citaId, valoracion }) => {
        const [rating, setRating] = useState(valoracion || 0);

        return (
            <div className="star-rating mt-4">

                {[1, 2, 3, 4, 5].map(star => (
                    <span
                        key={star}
                        className={`text-3xl cursor-pointer ${star <= rating ? 'text-yellow-400' : 'text-gray-300'}`}
                        onClick={() => {
                            setRating(star);
                            calificarCita(citaId, star);
                        }}
                    >
                        ★
                    </span>
                ))}
            </div>


        );
    };

    const tileClassName = ({ date }) => {
        const dayOfWeek = date.getDay();
        if (dayOfWeek === 0 || holidays.isHoliday(date)) {
            return 'day-no-disponible text-red-500';
        }
        return null;
    };

    return (
        <div
    style={{
        backgroundImage: `url('/images/barberia.jpg')`,
        backgroundSize: 'cover',
        backgroundAttachment: 'fixed',
    }}
>
    <NavigationCliente />

    <div className="container mx-auto p-8 bg-white bg-opacity-80 rounded-lg mt-10 max-w-2xl">
    <h2 className="text-4xl font-bold text-center mb-6">Próximas Citas</h2>
    <hr className="my-4 border-t-2 border-gray-300 w-full" />

                {showModificar && (
                    <div className="modify-cita-overlay fixed inset-0 bg-black bg-opacity-75 flex justify-center items-center z-50">
                        <div className="modify-cita p-6 bg-gray-100 rounded-lg shadow-md w-11/12 md:w-3/4 lg:w-1/2 max-w-2xl text-center">
                            <h2 className="text-3xl font-semibold mb-6">Modificar Cita</h2>
                            <div className="mb-6">
                                <h3 className="text-xl font-semibold mb-2">Selecciona un Servicio:</h3>
                                <div className="grid grid-cols-2 gap-4">
                                    {servicios.map((servicio) => (
                                        <div
                                            key={servicio.id}
                                            className={`servicio-card p-4 border ${selectedServicio?.id === servicio.id ? 'bg-blue-100' : 'bg-white'} rounded-lg cursor-pointer text-center`}
                                            onClick={() => handleSelectServicio(servicio)}
                                        >
                                            <h4 className="font-bold">{servicio.nombre}</h4>
                                            <p className="text-gray-500">{servicio.precio}€</p>
                                        </div>
                                    ))}
                                </div>
                            </div>
                            <div className="calendar-selection text-center mt-6">
                                <h3 className="text-xl font-semibold">Selecciona el Día:</h3>
                                <Calendar
                                    onChange={handleSelectDate}
                                    value={selectedDate}
                                    minDate={new Date()}
                                    tileClassName={tileClassName}
                                    className="mx-auto"
                                />
                            </div>
                            {selectedDate && horariosDisponibles.length > 0 && (
                                <div className="horarios-disponibles mt-4 grid grid-cols-4 gap-2 justify-center">
                                    {horariosDisponibles.map((hora) => (
                                        <button
                                            key={hora}
                                            onClick={() => handleConfirmModification(hora)}
                                            className="horario-button px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                                        >
                                            {hora}
                                        </button>
                                    ))}
                                </div>
                            )}
                            {selectedDate && horariosDisponibles.length === 0 && (
                                <p className="text-red-500 mt-4">No hay horarios disponibles para el día seleccionado.</p>
                            )}
                            <button
                                className="mt-6 bg-gray-500 text-white px-4 py-2 rounded"
                                onClick={() => setShowModificar(false)}
                            >
                                Cancelar Modificación
                            </button>
                        </div>
                    </div>
                )}

                {/* Próximas citas */}
                {citasOrdenadas.length > 0 ? (
                    <div className="flex flex-col gap-4 mt-6 items-center">
                        {citasOrdenadas.map((cita) => {
                            const fecha = dayjs(cita.fecha_hora_cita);
                            const mes = fecha.format('MMMM');
                            const dia = fecha.format('DD');
                            const hora = fecha.format('HH:mm');
                            const año = fecha.format('YYYY');

                            return (
                                <div key={cita.id} className="p-4 border rounded-lg shadow bg-white flex justify-between items-center w-full max-w-md">
                                    <div className="text-left">
                                        <p><strong>Método de Pago:</strong> {cita.metodo_pago === 'adelantado' ? 'PayPal' : 'Efectivo'}</p>
                                        <p><strong>Estado:</strong> <span className={getEstadoClass(cita.estado)}>{cita.estado.charAt(0).toUpperCase() + cita.estado.slice(1)}</span></p>
                                        <p><strong>Barbero:</strong> {cita.barbero?.nombre || 'No asignado'}</p>
                                        <div className="mt-4 flex gap-2">
                                            <button
                                                className="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600"
                                                onClick={() => cancelarCita(cita.id, cita.metodo_pago)}
                                            >
                                                Cancelar
                                            </button>
                                            <button
                                                className="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600"
                                                onClick={() => handleModifyClick(cita)}
                                            >
                                                Modificar
                                            </button>
                                        </div>
                                    </div>
                                    <div className="mx-4 border-l-2 border-gray-300 h-full"></div>
                                    <div className="flex flex-col items-center text-center" style={{ color: '#D2B48C' }}>
                                        <p className="text-2xl">{mes}</p>
                                        <p className="text-4xl font-bold">{dia}</p>
                                        <p className="text-xl">{hora}</p>
                                        <p className="text-xl">{año}</p>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                ) : (
                    <div className="flex items-center justify-center h-64">
                        <p className="text-xl text-gray-500 italic">No tienes citas programadas.</p>
                    </div>
                )}
        <br /><br /><br /><br />

                {/* Citas completadas o ausentes */}
                <div>
    <h2 className="text-4xl font-bold text-center mb-6">Citas Completadas</h2>
    <hr className="my-4 border-t-2 border-gray-300 w-full" />
    </div>
                {citasCompletadas.length > 0 ? (
                    <div className="flex flex-col gap-4 mt-6 items-center">
                        {citasCompletadas.map((cita) => {
                            const fecha = dayjs(cita.fecha_hora_cita);
                            const mes = fecha.format('MMMM');
                            const dia = fecha.format('DD');
                            const hora = fecha.format('HH:mm');
                            const año = fecha.format('YYYY');

                            return (
                                <div key={cita.id} className="p-4 border rounded-lg shadow bg-white flex justify-between items-center w-full max-w-md">
                                    <div className="text-left">
                                        <p><strong>Método de Pago:</strong> {cita.metodo_pago === 'adelantado' ? 'PayPal' : 'Efectivo'}</p>
                                        <p><strong>Estado:</strong> <span className={getEstadoClass(cita.estado)}>{cita.estado.charAt(0).toUpperCase() + cita.estado.slice(1)}</span></p>
                                        <p><strong>Barbero:</strong> {cita.barbero?.nombre || 'No asignado'}</p>
                                        {cita.estado === 'completada' && (
                                            <StarRating citaId={cita.id} valoracion={cita.valoracion} />
                                        )}
                                    </div>
                                    <div className="mx-4 border-l-2 border-gray-300 h-full"></div>
                                    <div className="flex flex-col items-center text-center" style={{ color: '#D2B48C' }}>
                                        <p className="text-2xl">{mes}</p>
                                        <p className="text-4xl font-bold">{dia}</p>
                                        <p className="text-xl">{hora}</p>
                                        <p className="text-xl">{año}</p>
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                ) : (
                    <div className="flex items-center justify-center h-64">
                        <p className="text-xl text-gray-500 italic">No tienes citas completadas.</p>
                    </div>
                )}
            </div>
            <br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
            <SobreNosotros />
            <Footer />
            <WhatsAppButton />
        </div>
    );
}
