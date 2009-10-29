project(browser)

cmake_minimum_required(VERSION 2.6)


if(QTEMBEDDED)
  #set(QT_INCLUDES /usr/local/Trolltech/include/)
  find_library(QT_LIBRARY NAMES QtWebKit PATHS /usr/local/Trolltech/QtEmbedded-4.5.1/lib/)
  message("${QT_LIBRARY}")
  message("Embedded")

else(QTEMBEDDED)
  find_package(Qt4 REQUIRED)
  message("Normal")
endif(QTEMBEDDED)

# This saves typing ${CMAKE_CURRENT_SOURCE_DIR} ${CMAKE_CURRENT_BINARY} in about every subdir
set(CMAKE_INCLUDE_CURRENT_DIR ON)

include_directories(qtermwidget/src/ ${QT_INCLUDES} ${QT_QTWEBKIT_INCLUDE_DIR})

add_subdirectory(qtermwidget)

set(browser_SRCS main.cpp mainwindow.cpp)

qt4_automoc(${browser_SRCS})

add_executable(browser ${browser_SRCS})

target_link_libraries(browser qtermwidget ${QT_QTWEBKIT_LIBRARIES} )

foreach(QT ${QT_LIBRARIES})
  message("${QT}")
endforeach(QT)
