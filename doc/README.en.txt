
The OpenGnSys Project     README
================================

OpenGnSys (pronounced Open Genesis) is based on a modular architecture separated in different service layers which allows its implementation in several educational and organizational IT environments, both in a centralized or distributed way. The main system management is carried out by means of a simple web interface which supports delegated administration. OpenGnSys is also suitable for isolated environments, being able to work under network failure conditions (offline mode).


Architecture
------------

From the point of view of clients, OpenGnSys Project is constituted by a set of separate modules in different layers of services.

 * The bottom layer is responsible for direct access to client devices and motor functions of cloning.
 * An intermediate layer consisting of a set of tools to perform complex tasks and environment customization.
 * Management module consists of a simple and intuitive web interface that allows common tasks to manage software distribution to clients. 

Overall, OpenGnSys is a modular system that integrates a set of interrelated components and can be adapted to a variety of work settings possible. These components consist of standard services (file servers, web, database, DHCP, PXE, ...) and custom-developed tools for the project (cloning engine, console web, browser ad hoc, management demons repository and server, ...) which in combination offer everything OpenGnSys system.

 * OpenGnSys Server
        Basic service cloning system (DHCP, NFS/Samba, PXE, database, etc..) Necessary for the boot process and initial configuration of the clients.

 * OpenGnSys Administrator 
        Centralized management web console (web pages, web services, etc.) with support for delegated administration, allowing control of all system components.

 * OpenGnSys Repository Manager
        Manager repository of images for each of the administrative units defined in the module OpenGnSys Administrator. There may be one or more modules depending on the model of the organization defined.

 * OpenGnSys Client Agent
        Executing agent of orders in the client, which integrates the following features:
        - Graphical interface to select options predefined by the administrator.
        - Interactive execution of Cloning Engine functions in administrator mode.
        - Execution of the tasks sent from the OpenGnSys Administrator module. 

 * OpenGnSys Clonning Engine
        Motor function libraries cloning, installation and startup of the client operating systems.

 * OpenGnSys Installer
        Software installation, upgrade and removal system which includes the settings generator, the clients boot generator, the component package builder, etc.


Scenarios
---------

OpenGnSys is a modular system that can be used in different scenarios within an Institution, both educational and organizational.

 * Educational Scenarios: OpenGnSys can lend support to some of the educational settings defined by the Working Group-Net Teaching RedIRIS.

    - Classroom teaching: allowing the management and implementation of software installation on the computers of teachers, with ability to customize the settings according to user requirements.
    - Classroom teaching ICT Classrooms: implementing methods independent support for PCs of students and teachers.
    - Self-learning ICT Classrooms: methods of deployment computers for students, grouped according to the needs identified for each position or for each user profile. 

 * Organizational Scenarios: The project can be implemented to provide support in other settings defined in an Institution, such as:

    - Information Services.
    - Helpdesk for employment computers.
    - Servers Deployment in Data Center. 


Origins

OpenGnSys born in May 2009 after the Working Groups RedIRIS held in Malaga as an effort to integrate several projects in development:

 * Brutalix, University of Zaragoza
 * Entorno Avanzado de Clonación (EAC), University of Málaga.
 * Web Hidra Interface, University of Seville. 


