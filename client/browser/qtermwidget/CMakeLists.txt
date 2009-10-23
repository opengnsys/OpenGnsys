add_definitions(-DHAVE_POSIX_OPENPT)

aux_source_directory(src qtermwidget_SRCS)

include_directories(${CMAKE_CURRENT_BINARY_DIR})

qt4_automoc(${qtermwidget_SRCS})

add_library(qtermwidget STATIC ${qtermwidget_SRCS})
