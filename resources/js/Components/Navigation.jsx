import React, { useState } from 'react';
import { Link } from '@inertiajs/react';

export default function Navigation() {
    const [isOpen, setIsOpen] = useState(false);

    const toggleMenu = () => {
        setIsOpen(!isOpen);
    };

    return (
        <nav className="bg-neutral-900 p-4">
            <div className="container mx-auto flex flex-wrap justify-between items-center">
                {/* Logo de la Barbería */}
                <div>
                    <Link href="/">
                        <img
                            src="/images/logo.png"
                            alt="Logo Barbería"
                            className="w-30 h-30 md:w-40 md:h-30"
                        />
                    </Link>
                </div>

                {/* Botón para abrir/cerrar el menú en dispositivos pequeños */}
                <button
                    onClick={toggleMenu}
                    className="text-white md:hidden block focus:outline-none"
                >
                    {isOpen ? (
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    ) : (
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            className="h-6 w-6"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth="2"
                                d="M4 6h16M4 12h16m-7 6h7"
                            />
                        </svg>
                    )}
                </button>

                {/* Menú de navegación (con rectángulo en pantallas grandes) */}
                <div
                    className={`${
                        isOpen ? 'block' : 'hidden'
                    } w-full md:w-auto md:block bg-neutral-900 md:bg-neutral-800 md:p-4 md:rounded-lg shadow-md font-serif w-full md:w-auto mt-4 md:mt-0`}
                    style={{ fontFamily: 'Times New Roman, serif' }}
                >
                    <ul className="flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4">
                        <li>
                            <Link
                                href={route('sobre-nosotros')}
                                className="text-xl md:text-3xl text-white hover:text-gray-400"
                            >
                                Sobre Nosotros
                            </Link>
                        </li>
                        <li>
                            <Link
                                href={route('servicios')}
                                className="text-xl md:text-3xl text-white hover:text-gray-400"
                            >
                                Servicios
                            </Link>
                        </li>
                        <li>
                            <Link
                                href={route('contacto')}
                                className="text-xl md:text-3xl text-white hover:text-gray-400"
                            >
                                Contacto
                            </Link>
                        </li>
                        <li>
                            <Link
                                href={route('equipo')}
                                className="text-xl md:text-3xl text-white hover:text-gray-400"
                            >
                                Equipo
                            </Link>
                        </li>
                    </ul>
                </div>

                {/* Botones de Iniciar sesión y Registrarse */}
                <div className="hidden md:flex flex-col md:flex-row space-y-4 md:space-y-0 md:space-x-4 font-serif mt-4 md:mt-0">
                    <Link
                        href="/login"
                        className="text-lg md:text-xl text-white hover:text-gray-400"
                    >
                        Iniciar sesión
                    </Link>
                    <Link
                        href="/register"
                        className="text-lg md:text-xl text-white hover:text-gray-400"
                    >
                        Registrarse
                    </Link>
                </div>
            </div>

            {/* Menú en pantallas pequeñas */}
            {isOpen && (
                <div className="md:hidden mt-4">
                    <div className="bg-neutral-800 p-4 rounded-lg shadow-md">
                        <div className="flex flex-col space-y-4">
                            <Link
                                href="/login"
                                className="text-lg text-white hover:text-gray-400"
                            >
                                Iniciar sesión
                            </Link>
                            <Link
                                href="/register"
                                className="text-lg text-white hover:text-gray-400"
                            >
                                Registrarse
                            </Link>
                        </div>
                    </div>
                </div>
            )}
        </nav>
    );
}