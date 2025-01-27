import React, { useState, useEffect } from 'react';
import NavigationInvitado from '../Components/NavigationInvitado';
import Calendar from 'react-calendar';
import 'react-calendar/dist/Calendar.css';
import WhatsAppButton from '@/Components/Wasa';
import SobreNosotros from '@/Components/Sobrenosotros';
import Footer from '../Components/Footer';
import axios from 'axios';
import Swal from 'sweetalert2';
import dayjs from 'dayjs';
import Holidays from 'date-holidays';

export default function Huecos() {
    const [step, setStep] = useState(1);
    const [selectedBarbero, setSelectedBarbero] = useState(null);
    const [selectedServicio, setSelectedServicio] = useState(null);
    const [selectedDate, setSelectedDate] = useState(null);
    const [selectedTime, setSelectedTime] = useState(null);
    const [servicios, setServicios] = useState([]);
    const [selectedServicioId, setSelectedServicioId] = useState(null);
    const [horariosDisponibles, setHorariosDisponibles] = useState([]);
    const [barberos, setBarberos] = useState([]);
    const holidays = new Holidays('ES', 'AN', 'CA');
    const today = dayjs().startOf('day');
    const minDate = today.toDate();  // Fecha actual para el calculo
    const maxDate = today.add(1, 'month').toDate();
    const [descansos, setDescansos] = useState([]);
    const [isLoadingCalendar, setIsLoadingCalendar] = useState(false); // Indica si el calendario está cargando
    const [diasSinCitas, setDiasSinCitas] = useState([]); // Días sin citas disponibles
    const [diasDescansoBarbero, setDiasDescansoBarbero] = useState([]);



    useEffect(() => {
        axios.get('/api/public/barberos')
            .then(response => {
                const barberosActivos = response.data.filter(barbero => barbero.estado === 'activo');
                setBarberos(barberosActivos);
                selectRandomBarberoAndService(barberosActivos);
            })
            .catch(error => console.error("Error al cargar los barberos:", error));
    }, []);


    const selectRandomBarberoAndService = async (barberosActivos) => {
        if (barberosActivos.length > 0) {
            const randomBarbero = barberosActivos[Math.floor(Math.random() * barberosActivos.length)];
            setSelectedBarbero(randomBarbero);
            axios
                .get(`/api/public/barberos/${randomBarbero.id}/servicios`)
                .then((response) => {
                    const serviciosDisponibles = response.data;
                    setServicios(serviciosDisponibles);
                    const randomServicio = serviciosDisponibles[Math.floor(Math.random() * serviciosDisponibles.length)];
                    setSelectedServicio(randomServicio);
                    setSelectedServicioId(randomServicio.id || randomServicio.servicio_id);
                    verificarDisponibilidadMensual();
                })
                .catch(() => {
                });
        }
    };




    useEffect(() => {
        // Llamada a la API para obtener los días de descanso
        axios.get('/api/public/descansos')
            .then(response => {
                setDescansos(response.data);
            })
            .catch(error => {
                console.error("Error al cargar los descansos:", error);
            });
    }, []);




    const handleSelectBarbero = (barbero) => {
        setSelectedBarbero(barbero);

        // Obtener servicios del barbero seleccionado
        axios
            .get(`/api/public/barberos/${barbero.id}/servicios`)
            .then((response) => {
                setServicios(response.data); // Actualizar servicios disponibles
                setStep(2); // Avanzar al siguiente paso
            })
            .catch((error) => {
                console.error("Error al cargar servicios del barbero:", error);
                Swal.fire("Error", "No se pudieron cargar los servicios del barbero.", "error");
            });

        // Obtener los días de descanso del barbero seleccionado
        axios.get(`/api/public/descansos/${barbero.id}`)
            .then(response => {
                setDiasDescansoBarbero(response.data);  // Actualizar días de descanso del barbero
            })
            .catch(error => {
                console.error("Error al cargar los días de descanso del barbero:", error);
                Swal.fire("Error", "No se pudieron cargar los días de descanso del barbero.", "error");
            });
    };




    const handleSelectServicio = (servicio) => {
        console.log("Servicio seleccionado:", servicio);
        setSelectedServicio(servicio);
        setSelectedServicioId(servicio.id || servicio.servicio_id);
        setStep(3);

        // Llamar a la función de disponibilidad
        verificarDisponibilidadMensual();
    };



    const handleSelectDate = (date) => {
        setSelectedDate(date);

        const formattedDate = dayjs(date).format('YYYY-MM-DD');
        const dayOfWeek = dayjs(date).day();

        // Verificar si la fecha seleccionada es un domingo o un festivo
        if (holidays.isHoliday(date) || dayOfWeek === 0) {
            Swal.fire({
                title: 'Fecha no disponible',
                text: 'No puedes reservar citas en domingos o festivos.',
                icon: 'error',
                confirmButtonText: 'Aceptar',
            });
            setHorariosDisponibles([]);
            return; // Terminar la función
        }

        // Verificar si los parámetros son válidos
        if (!selectedBarbero || !formattedDate || !selectedServicioId) {
            Swal.fire({
                title: 'Error',
                text: 'Debe seleccionar un barbero y un servicio antes de elegir la fecha.',
                icon: 'error',
                confirmButtonText: 'Aceptar',
            });
            return;
        }

        const API_BASE_URL = `${window.location.origin}`;

        // Mostrar los datos que se están enviando a la API
        console.log('Datos enviados a la API:', {
            barbero_id: selectedBarbero.id,
            fecha: formattedDate,
            servicio_id: selectedServicioId,
        });

        // Realizar la solicitud
        axios
            .get(`${API_BASE_URL}/api/public/citas/disponibilidad`, {
                params: {
                    barbero_id: selectedBarbero.id,
                    fecha: formattedDate,
                    servicio_id: selectedServicioId,
                },
            })
            .then((response) => {
                console.log("Respuesta de la API:", response.data);
                setHorariosDisponibles(response.data);
            })
            .catch((error) => {
                console.error("Error al obtener disponibilidad:", error);
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudo obtener la disponibilidad. Intente nuevamente.',
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                });
            });
    };

    const verificarDisponibilidadMensual = async () => {
        if (!selectedBarbero || !selectedServicioId) {
            console.warn("No hay barbero o servicio seleccionado. Saliendo de verificarDisponibilidadMensual.");
            return; // Salir si no están definidos
        }

        setIsLoadingCalendar(true);

        const fechas = [];
        for (let i = 0; i <= 30; i++) {
            const dia = dayjs().add(i, 'day').format('YYYY-MM-DD');
            fechas.push(dia);
        }

        const diasSinCitasTemp = [];

        try {
            await Promise.all(
                fechas.map(async (fecha) => {
                    try {
                        const response = await axios.get('/api/public/citas/disponibilidad', {
                            params: {
                                barbero_id: selectedBarbero.id,
                                servicio_id: selectedServicioId,
                                fecha: fecha,
                            },
                        });

                        if (!response.data || response.data.length === 0) {
                            diasSinCitasTemp.push(fecha);
                        }
                    } catch (error) {
                        console.error(`Error comprobando disponibilidad para ${fecha}:`, error);
                    }
                })
            );

            setDiasSinCitas(diasSinCitasTemp);
        } catch (error) {
            console.error("Error general verificando disponibilidad mensual:", error);
        } finally {
            setIsLoadingCalendar(false);
        }
    };








    const tileClassName = ({ date }) => {
    if (!descansos || !diasDescansoBarbero || !diasSinCitas) {
        return null; // Si el estado no está listo, no aplica ninguna clase
    }

    const dayOfWeek = dayjs(date).day();
    const dateStr = dayjs(date).format('YYYY-MM-DD');

    if (descansos.includes(dateStr)) {
        return 'day-no-disponible';
    }

    if (diasDescansoBarbero.includes(dateStr)) {
        return 'day-no-disponible';
    }

    if (holidays.isHoliday(date) || dayOfWeek === 0) {
        return 'day-no-disponible';
    }

    if (diasSinCitas.includes(dateStr)) {
        return 'day-sin-citas';
    }

    return null;
};


    const handleSelectHorario = (horario) => {
        Swal.fire({
            title: 'Inicia sesión o regístrate',
            text: 'Es necesario iniciar sesión para obtener una cita. ¡Regístrate ahora!',
            icon: 'info',
            showCancelButton: true,
            confirmButtonText: 'Iniciar Sesión',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirige al usuario a la página de inicio de sesión
                window.location.href = '/login';
            }
        });
    };


    const handleBack = () => {
        setStep((prevStep) => {
            const newStep = prevStep - 1;

            // Si está en el paso del calendario (paso 3) y vuelve atrás
            if (prevStep === 3) {
                // Recargar la página para volver al estado inicial
                window.location.reload();
            }

            // Restablecer el estado según el paso al que se regresa
            if (newStep === 1) {
                setSelectedBarbero(null); // Restablecer la selección del barbero
                setServicios([]); // Limpiar los servicios disponibles
            } else if (newStep === 2) {
                setSelectedServicio(null); // Restablecer la selección del servicio
                setSelectedServicioId(null); // Restablecer el ID del servicio seleccionado
                setSelectedDate(null); // Limpiar la fecha seleccionada
                setHorariosDisponibles([]); // Limpiar los horarios disponibles
            } else if (newStep === 3) {
                setSelectedTime(null); // Restablecer la hora seleccionada
            }

            return newStep;
        });
    };



    return (
        <div className="elegir-cita-background">
            <NavigationInvitado />
            <div className="container mx-auto p-8 bg-white rounded-lg mt-10 shadow-lg">
                <h2 className="text-4xl font-bold text-center mb-6">Reserve su Cita</h2>
                {step === 1 && (
                    <div className="barbero-selection">
                        <h3 className="text-2xl font-semibold text-center">¿Con quién quieres reservar la cita?</h3>
                        <div className="flex justify-around mt-6">
                        {barberos.map(barbero => {

    const imagenFinal = barbero.imagen ? `/storage/${barbero.imagen}` : '/images/default-avatar.png';

    return (
        <div
            key={barbero.id}
            className="barbero-card cursor-pointer hover:shadow-md transition-shadow rounded-lg p-4 text-center"
            onClick={() => handleSelectBarbero(barbero)}
        >
            <img
                src={imagenFinal}
                alt={barbero.nombre}
                className="rounded-full w-32 h-32 mx-auto"
            />
            <h4 className="text-xl mt-4">{barbero.nombre}</h4>
        </div>
    );
})}

                        </div>

                        <br /><br />
                        <button
    className="mt-6 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition"
    onClick={() => {
        window.location.href = '/invitado';
    }}
>
    Volver Atrás
</button>

                    </div>
                )}
                {step === 2 && (
                    <div className="servicio-selection">
                        <h3 className="text-2xl font-semibold text-center">Seleccione un Servicio:</h3>
                        <div className="grid grid-cols-3 gap-6 mt-6">
                            {servicios.map(servicio => (
                                <div
                                    key={servicio.id}
                                    className="servicio-card cursor-pointer p-4 text-center rounded-lg border border-gray-300 hover:bg-blue-100 transition"
                                    onClick={() => handleSelectServicio(servicio)} // Asegurarte que se pasa el objeto completo
                                >
                                    <h4 className="text-xl font-bold">{servicio.nombre}</h4>
                                    <h5 className="text-gray-600 mt-2 text-sm">{servicio.duracion} minutos</h5>
                                    <p className="text-gray-600 mt-2">{servicio.precio}€</p>
                                </div>

                            ))}
                        </div>
                        <button
                            className="mt-6 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition"
                            onClick={handleBack}
                        >
                            Volver Atrás
                        </button>
                    </div>
                )}
                {step === 3 && (
                    <div className="calendar-selection text-center">
                        <h3 className="text-2xl font-semibold">Selecciona el día:</h3>
                        <br /><br />
                        <div className="calendar-container mt-6 flex flex-col items-center">
                            {isLoadingCalendar ? (
                                <p className="text-gray-500 text-xl">Cargando calendario...</p>
                            ) : (
                                <Calendar
                                    onChange={handleSelectDate}
                                    value={selectedDate}
                                    minDate={minDate}
                                    maxDate={maxDate}
                                    tileClassName={tileClassName}
                                    tileDisabled={({ date }) => {
                                        const dateStr = dayjs(date).format('YYYY-MM-DD');

                                        // Deshabilitar los días que están en descansos
                                        if (descansos.includes(dateStr)) {
                                            return true;
                                        }

                                        // Deshabilitar domingos
                                        const dayOfWeek = dayjs(date).day();
                                        if (dayOfWeek === 0) {
                                            return true;
                                        }

                                        // No deshabilitar ningún otro día
                                        return false;
                                    }}
                                />)}
                            <style>
                                {`


    .day-sin-citas {
        background-color: #d6d8d9 !important;
        color: #6c757d !important;
        border-radius: 50% !important;
    }
    .day-sin-citas:hover {
        background-color: #c6c8ca !important;
    }
    `}
                            </style>

                        </div>
                        <br /><br /><br />

                        <div className="flex flex-col items-center mt-4">
    <p className="mt-2 text-gray-600 text-sm flex items-center">
        <span className="font-bold text-red-600 mr-2">🟥</span> Los días son festivos o días de descanso.
    </p>
    <p className="mt-2 text-gray-600 text-sm flex items-center">
        <span className="font-bold text-gray-600 mr-2">🔘</span> Los días no tienen citas disponibles.
    </p>
</div>


                        <br /><br />
                        {selectedDate && (
                            <div className="horarios-disponibles mt-4">
                                <h3 className="text-2xl font-semibold">Horarios disponibles:</h3>
                                {horariosDisponibles.length > 0 ? (
                                    <div className="horarios-list mt-4 grid grid-cols-4 gap-4">
                                        {horariosDisponibles.map((horario) => (
                                            <div
                                                key={horario}
                                                className="horario-item p-2 bg-blue-500 text-white rounded cursor-pointer"
                                                onClick={() => handleSelectHorario(horario)}
                                            >
                                                {horario}
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p className="text-red-500 mt-4">No hay horarios disponibles para esta fecha.</p>
                                )}

                            </div>
                        )}
                        <button
                            className="mt-6 px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600 transition"
                            onClick={handleBack}
                        >
                            Volver Atrás
                        </button>
                    </div>
                )}

            </div>
            <br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
            <SobreNosotros />
            <Footer />
            <WhatsAppButton />
        </div>
    );
}
