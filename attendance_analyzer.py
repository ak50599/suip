#!/usr/bin/env python3
"""
attendance_analyzer.py - سكربت بايثون لتحليل سجلات الحضور والغياب
يقوم هذا السكربت بتحليل البيانات من MySQL وإصدار تقارير عن الطلاب المعرضين للحرمان
"""

import mysql.connector
import pandas as pd
from datetime import datetime, timedelta
import sys
import json

# إعدادات الاتصال بقاعدة البيانات
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'learnata_clone'
}

def get_connection():
    """إنشاء اتصال بقاعدة البيانات"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except mysql.connector.Error as err:
        print(f"❌ خطأ في الاتصال بقاعدة البيانات: {err}")
        sys.exit(1)

def analyze_attendance(threshold=15):
    """
    تحليل سجلات الحضور وتحديد الطلاب الذين تجاوزوا نسبة الغياب المحددة
    
    Args:
        threshold (int): نسبة الغياب المسموحة (الافتراضي 15%)
    """
    conn = get_connection()
    
    try:
        print("=" * 60)
        print(f"تحليل سجلات الحضور - عتبة الغياب: {threshold}%")
        print("=" * 60)
        
        # جلب بيانات المحاضرات والطلاب
        query = """
            SELECT 
                l.lecture_id,
                l.lecture_title,
                l.lecture_date,
                l.course_id,
                ca.college_name,
                COUNT(DISTINCT a.student_id) as total_students,
                SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count
            FROM lectures l
            LEFT JOIN attendance a ON l.lecture_id = a.lecture_id
            LEFT JOIN college_admission_rates ca ON l.course_id = ca.id
            WHERE l.status = 'completed'
            GROUP BY l.lecture_id, l.lecture_title, l.lecture_date, l.course_id, ca.college_name
            ORDER BY l.lecture_date DESC
        """
        
        df = pd.read_sql(query, conn)
        
        if df.empty:
            print("⚠️ لا توجد محاضرات مكتملة في النظام")
            return
        
        print(f"\n📊 إجمالي المحاضرات المكتملة: {len(df)}")
        
        # تحليل حضور كل طالب في كل مادة
        student_query = """
            SELECT 
                s.student_id,
                u.full_name as student_name,
                u.email as student_email,
                l.lecture_title,
                l.course_id,
                ca.college_name,
                a.status,
                a.attendance_date
            FROM attendance a
            JOIN students s ON a.student_id = s.student_id
            JOIN users u ON s.user_id = u.id
            JOIN lectures l ON a.lecture_id = l.lecture_id
            JOIN college_admission_rates ca ON l.course_id = ca.college_id
            WHERE l.status = 'completed'
            ORDER BY s.student_id, l.course_id, a.attendance_date
        """
        
        student_df = pd.read_sql(student_query, conn)
        
        if student_df.empty:
            print("⚠️ لا توجد سجلات حضور للطلاب")
            return
        
        print(f"📊 إجمالي سجلات الحضور: {len(student_df)}")
        
        # حساب إحصائيات لكل طالب في كل مادة
        stats = student_df.groupby(['student_id', 'student_name', 'student_email', 'college_name']).agg({
            'status': ['count', lambda x: (x == 'present').sum(), lambda x: (x == 'absent').sum()]
        }).reset_index()
        
        stats.columns = ['student_id', 'student_name', 'student_email', 'college_name', 
                        'total_lectures', 'present_count', 'absent_count']
        
        # حساب نسبة الغياب
        stats['absence_percentage'] = (stats['absent_count'] / stats['total_lectures'] * 100).round(2)
        
        # تحديد الطلاب المعرضين للحرمان
        at_risk = stats[stats['absence_percentage'] >= threshold].copy()
        
        print("\n" + "=" * 60)
        print("📋 تقرير الطلاب المعرضين للحرمان")
        print("=" * 60)
        
        if at_risk.empty:
            print(f"✅ لا يوجد طلاب تجاوزوا نسبة الغياب المحددة ({threshold}%)")
        else:
            print(f"\n⚠️ عدد الطلاب المعرضين للحرمان: {len(at_risk)}\n")
            
            # عرض التقرير
            print(at_risk[['student_name', 'college_name', 'total_lectures', 
                          'absent_count', 'absence_percentage']].to_string(index=False))
            
            # حفظ التقرير في ملف
            report_file = f"attendance_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.csv"
            at_risk.to_csv(report_file, index=False, encoding='utf-8-sig')
            print(f"\n💾 تم حفظ التقرير في: {report_file}")
            
            # إرسال تنبيهات (محاكاة)
            print("\n📧 إرسال تنبيهات للطلاب:")
            for _, row in at_risk.iterrows():
                print(f"  - {row['student_name']} ({row['student_email']}): نسبة الغياب {row['absence_percentage']}%")
        
        # إحصائيات عامة
        print("\n" + "=" * 60)
        print("📈 إحصائيات عامة")
        print("=" * 60)
        print(f"إجمالي الطلاب: {stats['student_id'].nunique()}")
        print(f"متوسط نسبة الحضور: {stats['absence_percentage'].mean():.2f}%")
        print(f"أعلى نسبة غياب: {stats['absence_percentage'].max():.2f}%")
        print(f"أقل نسبة غياب: {stats['absence_percentage'].min():.2f}%")
        
    except Exception as e:
        print(f"❌ خطأ أثناء التحليل: {e}")
    finally:
        if conn.is_connected():
            conn.close()

def generate_daily_report(date=None):
    """
    إنشاء تقرير يومي للحضور
    
    Args:
        date (str): التاريخ (YYYY-MM-DD)، الافتراضي اليوم
    """
    if date is None:
        date = datetime.now().strftime('%Y-%m-%d')
    
    conn = get_connection()
    
    try:
        query = """
            SELECT 
                l.lecture_title,
                ca.college_name,
                COUNT(DISTINCT a.student_id) as total_attended,
                SUM(CASE WHEN a.location_verified = 1 THEN 1 ELSE 0 END) as verified_location
            FROM attendance a
            JOIN lectures l ON a.lecture_id = l.lecture_id
            JOIN college_admission_rates ca ON l.course_id = ca.college_id
            WHERE a.attendance_date = %s
            GROUP BY l.lecture_title, ca.college_name
            ORDER BY total_attended DESC
        """
        
        df = pd.read_sql(query, conn, params=(date,))
        
        print(f"\n📊 تقرير الحضور اليومي - {date}")
        print("=" * 60)
        
        if df.empty:
            print("⚠️ لا توجد سجلات حضور لهذا التاريخ")
        else:
            print(df.to_string(index=False))
            
    except Exception as e:
        print(f"❌ خطأ أثناء إنشاء التقرير: {e}")
    finally:
        if conn.is_connected():
            conn.close()

if __name__ == "__main__":
    # تشغيل التحليل الافتراضي
    if len(sys.argv) > 1:
        threshold = int(sys.argv[1])
    else:
        threshold = 15
    
    analyze_attendance(threshold)
    
    # اختياري: تقرير يومي
    # generate_daily_report()
