"""Genera las capturas del módulo de catálogo contra http://localhost:8090 (Firefox headless)."""
import os, time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.firefox.options import Options

URL = "http://localhost:8090"
OUT = os.path.dirname(os.path.abspath(__file__))
o = Options(); o.add_argument("-headless"); o.add_argument("--width=1400"); o.add_argument("--height=1000")
o.binary_location = "/snap/firefox/current/usr/lib/firefox/firefox"
d = webdriver.Firefox(options=o)
d.set_window_size(1400, 1000)
w = lambda s=0.6: time.sleep(s)
shot = lambda n: (w(), d.save_screenshot(f"{OUT}/{n}.png"))
byid = lambda i: d.find_element(By.ID, i)

def login(email):
    d.get(URL + "/login")
    byid("email").send_keys(email); byid("password").send_keys("secreto123")
    d.find_element(By.CSS_SELECTOR, "button[type=submit]").click(); w(1)

def limpiar():
    d.execute_script("const e=document.getElementById('f-buscar'); e.value=''; e.dispatchEvent(new Event('input'));"); w(0.3)

def abrir_form():
    byid("btn-nueva").click(); w()

def llenar(clave, nombre, cr, sem):
    for i, v in (("in-clave", clave), ("in-nombre", nombre), ("in-creditos", cr), ("in-semestre", sem)):
        e = byid(i); e.clear(); e.send_keys(v)

def fila_btn(clave, accion):
    w(0.5)
    for tr in d.find_elements(By.CSS_SELECTOR, "#tabla-cuerpo tr"):
        celdas = tr.find_elements(By.CSS_SELECTOR, "td strong")
        if celdas and celdas[0].text == clave:
            return tr.find_element(By.CSS_SELECTOR, f"button[data-accion={accion}]")

# 0. Control de acceso: alumno -> 403
login("ana.ramirez@itcelaya.edu.mx"); d.get(URL + "/catalogo-materias"); shot("01_acceso_alumno_403")
d.get(URL + "/logout")
# 1. Admin, lista
d.delete_all_cookies(); login("roberto.salinas@itcelaya.edu.mx"); w(1); shot("02_lista_catalogo")
# 2. Campos vacíos
abrir_form(); byid("btn-guardar-form").click(); shot("03_alta_campos_vacios")
# 3. Formatos inválidos
llenar("AB-1", "12345", "15", "12"); byid("btn-guardar-form").click(); shot("04_alta_formatos_invalidos")
# 4. Clave duplicada (SCD1008 equivale a SCD-1008 del plan)
llenar("SCD1008", "Fundamentos de Programación II", "5", "1"); byid("btn-guardar-form").click(); shot("05_clave_duplicada")
# 5. Alta válida con prerrequisito
llenar("TST1001", "Seguridad en Aplicaciones Web", "4", "8")
byid("btn-agregar-pre").click(); w(0.3)
sel = d.find_element(By.CSS_SELECTOR, "#contenedor-prerrequisitos select")
sel.find_elements(By.TAG_NAME, "option")[1].click(); w(0.3)
byid("btn-guardar-form").click(); w(1); shot("06_alta_exitosa")
# 6. Edición con confirmación (créditos y semestre)
d.execute_script("window.scrollTo(0,0)")
byid("f-buscar").send_keys("TST1001"); w()
fila_btn("TST1001", "editar").click(); w()
byid("in-creditos").clear(); byid("in-creditos").send_keys("5")
byid("in-semestre").clear(); byid("in-semestre").send_keys("9")
byid("btn-guardar-form").click(); shot("07_edicion_requiere_confirmacion")
byid("btn-guardar-form").click(); w(1); shot("08_edicion_guardada")
# 7. Eliminación física de TST1001
fila_btn("TST1001", "eliminar").click(); w(1); shot("09_dialogo_eliminacion_fisica")
byid("btn-confirmar-eliminar").click(); w(1)
limpiar()
# 8. Eliminación bloqueada (prerrequisito de otra)
byid("f-buscar").send_keys("SCD-1008"); w(); fila_btn("SCD-1008", "eliminar").click(); w(1); shot("10_eliminacion_bloqueada")
byid("btn-del-cerrar").click(); limpiar()
# 9. Baja lógica (materia con historial y sin dependientes)
cand = None
for tr in d.find_elements(By.CSS_SELECTOR, "#tabla-cuerpo tr"):
    t = tr.text
    if tr.find_elements(By.CSS_SELECTOR, "td strong") and "alumnos con historial" in t and "Prerrequisito de" not in t and "Activa" in t:
        cand = tr.find_element(By.CSS_SELECTOR, "td strong").text; break
byid("f-buscar").send_keys(cand); w(); fila_btn(cand, "eliminar").click(); w(1); shot("11_dialogo_baja_logica")
byid("btn-confirmar-eliminar").click(); w(1)
byid("f-estatus").send_keys("Solo Inactivas"); w(); shot("12_baja_logica_aplicada")
# 10. Reflejo en la retícula
d.get(URL + "/reticula"); w(1.5); shot("13_reticula_sin_materia_dada_de_baja")
print("Materia dada de baja:", cand)
d.get(URL + "/catalogo-materias"); w(1)
byid("btn-bitacora").click(); w(1); shot("14_bitacora_auditoria")
d.quit()
