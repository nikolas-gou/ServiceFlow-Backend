SELECT repairs.*, 
        customers.id AS customer_id, 
        customers.type, 
        customers.name, 
        customers.email, 
        customers.phone, 
        customers.created_at AS customer_created_at,
        motors.id AS motor_id, 
        motors.serial_number, 
        motors.manufacturer, 
        motors.kw, 
        motors.hp, 
        motors.rpm, 
        motors.step, 
        motors.half_step,
        motors.helper_step,
        motors.helper_half_step,
        motors.spiral, 
        motors.half_spiral,
        motors.helper_spiral,
        motors.helper_half_spiral,
        motors.connectionism, 
        motors.volt, 
        motors.poles,
        motors.type_of_step,
        motors.type_of_motor,
        motors.type_of_volt,
        motors.created_at AS motor_created_at,
        motors.customer_id AS motor_customer_id,
        JSON_ARRAYAGG(
            CASE 
                WHEN repair_fault_links.repair_id IS NOT NULL AND repair_fault_links.common_fault_id IS NOT NULL
                THEN JSON_OBJECT(
                    'repair_id', repair_fault_links.repair_id,
                    'common_fault_id', repair_fault_links.common_fault_id
                )
                ELSE NULL
            END
        ) AS repair_fault_links_json,
        -- Συλλογή όλων των διατομών σε JSON format
        JSON_ARRAYAGG(
            CASE 
                WHEN motor_cross_section_links.id IS NOT NULL 
                THEN JSON_OBJECT(
                    'id', motor_cross_section_links.id,
                    'motor_id', motor_cross_section_links.motor_id,
                    'cross_section', motor_cross_section_links.cross_section,
                    'type', motor_cross_section_links.type
                )
                ELSE NULL
            END
        ) AS cross_sections_json

FROM repairs
INNER JOIN customers ON repairs.customer_id = customers.id
INNER JOIN motors ON repairs.motor_id = motors.id
LEFT JOIN motor_cross_section_links ON motors.id = motor_cross_section_links.motor_id
LEFT JOIN repair_fault_links ON repairs.id = repair_fault_links.repair_id
GROUP BY repairs.id, customers.id, motors.id
ORDER BY repairs.created_at DESC
    