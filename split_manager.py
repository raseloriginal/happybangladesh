import os
import re

file_path = r'c:\xampp\htdocs\happybangladesh\modules\Manager\ManagerController.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

def extract_method(method_name):
    match = re.search(r'^[ \t]*public function ' + method_name + r'\s*\(', content, re.MULTILINE)
    if not match:
        print(f"Warning: Method {method_name} not found!")
        return ""
    start_idx = match.start()
    
    line_start = content.rfind('\n', 0, start_idx)
    
    brace_level = 0
    in_method = False
    end_idx = -1
    
    for i in range(start_idx, len(content)):
        if content[i] == '{':
            brace_level += 1
            in_method = True
        elif content[i] == '}':
            brace_level -= 1
            if in_method and brace_level == 0:
                end_idx = i + 1
                break
                
    if end_idx == -1:
        return ""
        
    return content[line_start:end_idx].strip() + "\n"

def create_controller(class_name, methods):
    class_code = "<?php\n\nclass " + class_name + " extends Controller\n{\n"
    class_code += "    protected string $viewPath;\n"
    class_code += "    private PDO $db;\n\n"
    class_code += "    public function __construct()\n"
    class_code += "    {\n"
    class_code += "        RoleMiddleware::check([ROLE_ADMIN, ROLE_MANAGER]);\n"
    class_code += "        $this->viewPath = MOD_PATH . '/Manager/views';\n"
    class_code += "        $this->db = Database::getInstance();\n"
    class_code += "    }\n\n"
    
    for method in methods:
        method_code = extract_method(method)
        if method_code:
            indented = "\n".join("    " + line if line else "" for line in method_code.split("\n"))
            class_code += indented + "\n\n"
            
    class_code += "}\n"
    return class_code

mapping = {
    'ProductController': [
        'products', 'apiProductStore', 'apiProductUpdate', 'apiProductDelete', 
        'apiAdjustBuyingPrice', 'apiProductPriceHistory', 'apiStockAdjust',
        'categories', 'apiCategoryStore', 'apiCategoryUpdate', 'apiCategoryDelete',
        'lots', 'apiLotStore', 'apiLotUpdate', 'apiLotDelete', 'apiLotBatchDelete', 
        'apiLotBatchUpdate', 'apiLotBatchEditRequest'
    ],
    'InventoryController': [
        'inventory'
    ],
    'DispatchController': [
        'dispatch', 'apiDispatchData', 'apiDispatchNewPopupData', 'apiDispatchAssign',
        'apiDispatchUpdateDsr', 'apiDispatchUpdateDeliveryDate', 'apiDispatchDelete',
        'apiDispatchSrDetails', 'apiDispatchCompanyDetails', 'apiDispatchOrganizeData',
        'apiDispatchOrganizeSave', 'apiDispatchStatusUpdate', 'apiDispatchUndoDispatch',
        'apiDispatchUpdateProductQty', 'apiDispatchVanStock', 'apiDispatchReturnSave',
        'apiDispatchUndoReturn'
    ],
    'OperationsController': [
        'operations', 'apiOperationsOrders', 'apiOperationsDeliveries', 'apiOperationsDsrDeliveries',
        'apiOperationsDsrDeliveryAction', 'apiOperationsEditOrder', 'apiOperationsBulkChangeOrderDate',
        'apiOperationsDeleteOrder', 'apiOperationsEditDelivery', 'apiOperationsPlaceOrder',
        'apiOperationsMakeDelivery', 'apiSrCutoffStatus', 'apiUndoOrderCutoff'
    ],
    'AttendanceController': [
        'attendance', 'attendanceStore', 'apiAttendanceQrGet', 'apiAttendanceQrGenerate'
    ],
    'SettlementController': [
        'settlements', 'apiSettlementUpdate'
    ],
    'ReportController': [
        'readysale', 'readysaleStore', 'orders', 'apiOrdersCompanies', 'apiOrdersSrs', 'apiOrdersProducts'
    ]
}

out_dir = r'c:\xampp\htdocs\happybangladesh\modules\Manager'

for class_name, methods in mapping.items():
    code = create_controller(class_name, methods)
    with open(os.path.join(out_dir, class_name + '.php'), 'w', encoding='utf-8') as f:
        f.write(code)
    print(f"Created {class_name}.php with {len(methods)} methods.")

print("Done generating new controllers.")
