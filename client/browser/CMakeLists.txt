project(browser)

cmake_minimum_required(VERSION 2.6)

find_package(Qt4 REQUIRED)

include_directories(qtermwidget/src/ ${QT_INCLUDES} ${QT_QTWEBKIT_INCLUDE_DIR} ${CMAKE_CURRENT_BINARY_DIR})

add_subdirectory(qtermwidget)

set(browser_SRCS main.cpp mainwindow.cpp)

qt4_automoc(${browser_SRCS})

add_executable(browser ${browser_SRCS})

target_link_libraries(browser qtermwidget ${QT_LIBRARIES} ${QT_QTWEBKIT_LIBRARIES} )
